<?php

declare(strict_types=1);

namespace Saloon\Traits\Connector;

use LogicException;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Helpers\Helpers;
use InvalidArgumentException;
use GuzzleHttp\Promise\Promise;
use Saloon\Helpers\RetryHelper;
use Saloon\Http\PendingRequest;
use Saloon\Http\Faking\MockClient;
use GuzzleHttp\Promise\PromiseInterface;
use Saloon\Traits\RequestProperties\Retryable;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Exceptions\Request\FatalRequestException;

trait SendsRequests
{
    use ManagesFakeResponses;

    /**
     * Send a request synchronously
     *
     * @param callable(\Throwable, \Saloon\Http\Request): (bool)|null $handleRetry
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function send(Request $request, MockClient $mockClient = null, callable $handleRetry = null): Response
    {
        if (is_null($handleRetry)) {
            $handleRetry = static fn (): bool => true;
        }

        $maxTries = RetryHelper::getMaxTries($this, $request);
        $retryInterval = RetryHelper::getRetryInterval($this, $request);
        $throwOnMaxTries = RetryHelper::getThrowOnMaxTries($this, $request);

        $attempts = 0;

        while ($attempts < $maxTries) {
            $attempts++;

            // When the current attempt is greater than one, we will wait
            // the interval (if it has been provided)

            if ($attempts > 1) {
                usleep($retryInterval * 1000);
            }

            try {
                $pendingRequest = $this->createPendingRequest($request, $mockClient);

                if ($pendingRequest->hasFakeResponse()) {
                    $response = $this->createFakeResponse($pendingRequest);
                } else {
                    $response = $this->sender()->send($pendingRequest);
                }

                // We'll execute the response pipeline now so that all the response
                // middleware can be run before we throw any exceptions.

                $response = $pendingRequest->executeResponsePipeline($response);

                // We'll check if our tries is greater than one. If it is, then we will
                // force an exception to be thrown if the request was unsuccessful.
                // This will then force our catch handler to retry the request.

                if ($maxTries > 1) {
                    $response->throw();
                }

                return $response;
            } catch (FatalRequestException|RequestException $exception) {
                // We'll attempt to get the response from the exception. We'll only be able
                // to do this if the exception was a "RequestException".

                $exceptionResponse = $exception instanceof RequestException ? $exception->getResponse() : null;

                // If we've reached our max attempts - we won't try again, but we'll either
                // return the last response made or just throw an exception.

                if ($attempts === $maxTries) {
                    return isset($exceptionResponse) && $throwOnMaxTries === false ? $exceptionResponse : throw $exception;
                }

                // Now we'll run the "handleRetry" method on both the connector and the request.
                // This method will return a boolean. If just one of the objects returns false
                // then we won't handle the retry.

                $allowRetry = $handleRetry($exception, $request)
                    && $request->handleRetry($exception, $request)
                    && $this->handleRetry($exception, $request);

                // If we cannot retry we will simply return the response or throw the exception.

                if ($allowRetry === false) {
                    return isset($exceptionResponse) && $throwOnMaxTries === false ? $exceptionResponse : throw $exception;
                }
            }
        }

        throw new LogicException('The request was not sent.');
    }

    /**
     * Send a request asynchronously
     */
    public function sendAsync(Request $request, MockClient $mockClient = null): PromiseInterface
    {
        $sender = $this->sender();

        // We'll wrap the following logic in our own Promise which means we won't
        // build up our PendingRequest until the promise is actually being sent
        // this is great because our middleware will only run right before
        // the request is sent.

        return $promise = new Promise(function () use (&$promise, $request, $mockClient, $sender) {
            $pendingRequest = $this->createPendingRequest($request, $mockClient)->setAsynchronous(true);

            // We need to check if the Pending Request contains a fake response.
            // If it does, then we will create the fake response. Otherwise,
            // we'll send the request.

            if ($pendingRequest->hasFakeResponse()) {
                $requestPromise = $this->createFakeResponse($pendingRequest);
            } else {
                $requestPromise = $sender->sendAsync($pendingRequest);
            }

            $requestPromise->then(fn (Response $response) => $pendingRequest->executeResponsePipeline($response));

            // We'll resolve the promise's value with another promise.
            // We will use promise chaining as Guzzle's will fulfill
            // the request promise.

            $promise->resolve($requestPromise);
        });
    }

    /**
     * Send a synchronous request and retry if it fails
     *
     * @param callable(\Throwable, \Saloon\Http\Request): (bool)|null $handleRetry
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function sendAndRetry(Request $request, int $tries, int $interval = 0, callable $handleRetry = null, bool $throw = true, MockClient $mockClient = null): Response
    {
        if (! array_key_exists(Retryable::class, Helpers::classUsesRecursive($request))) {
            throw new InvalidArgumentException('The request class must use the "Retryable" trait.');
        }

        $request->tries = $tries;
        $request->retryInterval = $interval;
        $request->throwOnMaxTries = $throw;

        return $this->send($request, $mockClient, $handleRetry);
    }

    /**
     * Create a new PendingRequest
     *
     * @throws \ReflectionException
     */
    public function createPendingRequest(Request $request, MockClient $mockClient = null): PendingRequest
    {
        return new PendingRequest($this, $request, $mockClient);
    }
}
