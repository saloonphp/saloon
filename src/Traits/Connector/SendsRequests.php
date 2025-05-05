<?php

declare(strict_types=1);

namespace Saloon\Traits\Connector;

use LogicException;
use Saloon\Http\Pool;
use Saloon\Http\Request;
use Saloon\Http\Response;
use GuzzleHttp\Promise\Utils;
use GuzzleHttp\Promise\Promise;
use Saloon\Http\PendingRequest;
use Saloon\Http\Faking\MockClient;
use GuzzleHttp\Promise\PromiseInterface;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Exceptions\Request\FatalRequestException;

trait SendsRequests
{
    use HasSender;
    use ManagesFakeResponses;

    /**
     * Send a request synchronously
     *
     * @param callable(\Throwable, \Saloon\Http\Request): (bool)|null $handleRetry
     */
    public function send(Request $request, ?MockClient $mockClient = null, ?callable $handleRetry = null): Response
    {
        if (is_null($handleRetry)) {
            $handleRetry = static fn (): bool => true;
        }

        $attempts = 0;

        $maxTries = $request->tries ?? $this->tries ?? 1;
        $retryInterval = $request->retryInterval ?? $this->retryInterval ?? 0;
        $throwOnMaxTries = $request->throwOnMaxTries ?? $this->throwOnMaxTries ?? true;
        $useExponentialBackoff = $request->useExponentialBackoff ?? $this->useExponentialBackoff ?? false;

        if ($maxTries <= 0) {
            $maxTries = 1;
        }

        if ($retryInterval <= 0) {
            $retryInterval = 0;
        }

        while ($attempts < $maxTries) {
            $attempts++;

            // When the current attempt is greater than one, we will wait
            // the interval (if it has been provided)

            if ($attempts > 1) {
                $sleepTime = $useExponentialBackoff
                    ? $retryInterval * (2 ** ($attempts - 2)) * 1000
                    : $retryInterval * 1000;

                usleep($sleepTime);
            }

            try {
                $pendingRequest = $this->createPendingRequest($request, $mockClient);

                // 🚀 ... 🪐  ... 💫

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

                // If the exception is a FatalRequestException, we'll execute the fatal pipeline
                if ($exception instanceof FatalRequestException) {
                    $exception->getPendingRequest()->executeFatalPipeline($exception);
                }

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
    public function sendAsync(Request $request, ?MockClient $mockClient = null): PromiseInterface
    {
        $sender = $this->sender();

        // We'll wrap the following logic in our own Promise which means we won't
        // build up our PendingRequest until the promise is actually being sent
        // this is great because our middleware will only run right before
        // the request is sent.

        return Utils::task(function () use ($request, $mockClient, $sender) {
            $pendingRequest = $this->createPendingRequest($request, $mockClient)->setAsynchronous(true);

            // We need to check if the Pending Request contains a fake response.
            // If it does, then we will create the fake response. Otherwise,
            // we'll send the request.

            // 🚀 ... 🪐  ... 💫

            if ($pendingRequest->hasFakeResponse()) {
                $requestPromise = $this->createFakeResponse($pendingRequest);
            } else {
                $requestPromise = $sender->sendAsync($pendingRequest);
            }

            $requestPromise->then(fn (Response $response) => $pendingRequest->executeResponsePipeline($response));

            return $requestPromise;
        });
    }

    /**
     * Send a synchronous request and retry if it fails
     *
     * @deprecated This method will be removed in Saloon v4. Please refer to the documentation to see connector or request-based retry functionality.
     *
     * @param callable(\Throwable, \Saloon\Http\Request): (bool)|null $handleRetry
     */
    public function sendAndRetry(Request $request, int $tries, int $interval = 0, ?callable $handleRetry = null, bool $throw = true, ?MockClient $mockClient = null, bool $useExponentialBackoff = false): Response
    {
        $request->tries = $tries;
        $request->retryInterval = $interval;
        $request->throwOnMaxTries = $throw;
        $request->useExponentialBackoff = $useExponentialBackoff;

        return $this->send($request, $mockClient, $handleRetry);
    }

    /**
     * Create a new PendingRequest
     */
    public function createPendingRequest(Request $request, ?MockClient $mockClient = null): PendingRequest
    {
        return new PendingRequest($this, $request, $mockClient);
    }

    /**
     * Create a request pool
     *
     * @param iterable<\GuzzleHttp\Promise\PromiseInterface|\Saloon\Http\Request>|callable(\Saloon\Http\Connector): iterable<\GuzzleHttp\Promise\PromiseInterface|\Saloon\Http\Request> $requests
     * @param int|callable(int $pendingRequests): (int) $concurrency
     * @param callable(\Saloon\Http\Response, array-key $key, \GuzzleHttp\Promise\PromiseInterface $poolAggregate): (void)|null $responseHandler
     * @param callable(mixed $reason, array-key $key, \GuzzleHttp\Promise\PromiseInterface $poolAggregate): (void)|null $exceptionHandler
     */
    public function pool(iterable|callable $requests = [], int|callable $concurrency = 5, callable|null $responseHandler = null, callable|null $exceptionHandler = null): Pool
    {
        return new Pool($this, $requests, $concurrency, $responseHandler, $exceptionHandler);
    }
}
