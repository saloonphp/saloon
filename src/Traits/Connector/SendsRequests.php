<?php

declare(strict_types=1);

namespace Saloon\Traits\Connector;

use LogicException;
use Saloon\Contracts\Request;
use Saloon\Contracts\Response;
use GuzzleHttp\Promise\Promise;
use Saloon\Http\PendingRequest;
use Saloon\Contracts\MockClient;
use GuzzleHttp\Promise\PromiseInterface;
use Saloon\Exceptions\PendingRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Exceptions\InvalidResponseClassException;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Contracts\PendingRequest as PendingRequestContract;

trait SendsRequests
{
    use CreatesFakeResponses;

    /**
     * Send a request
     *
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function send(Request $request, MockClient $mockClient = null): Response
    {
        $nextRequest = clone $request;

        $tries = $request->tries ?? $this->tries ?? 1;
        $retryInterval = $request->retryInterval ?? $this->retryInterval ?? 0;
        $throwOnMaxTries = $request->throwOnMaxTries ?? $this->throwOnMaxTries ?? true;

        $currentAttempt = 0;

        while ($currentAttempt < $tries) {
            $currentAttempt++;

            // When the current attempt is greater than one, we will wait
            // the interval (if it has been provided)

            if ($currentAttempt > 1) {
                usleep($retryInterval * 1000);
            }

            try {
                // Let's start by creating the PendingRequest for the current attempt.
                // after that, we will send the request.

                $pendingRequest = $this->createPendingRequest($nextRequest, $mockClient);

                if ($pendingRequest->hasFakeResponse()) {
                    $response = $this->createFakeResponse($pendingRequest);
                } else {
                    $response = $this->sender()->send($pendingRequest);
                }

                // We'll check if our tries is greater than one. If it is, then that
                // means we will force an exception to be thrown if the request was
                // unsuccessful. This will then force our catch handler to retry
                // the request.

                if ($tries > 1) {
                    $response->throw();
                }

                // We'll return the response if the exception wasn't thrown.

                return $response;
            } catch (FatalRequestException|RequestException $exception) {
                // We'll attempt to get the response from the exception. We'll only be able
                // to do this if the exception was a "RequestException".

                $exceptionResponse = $exception instanceof RequestException ? $exception->getResponse() : null;

                // If we've reached our max attempts - we won't try again, but we'll either
                // return the last response made or just throw an exception.

                if ($currentAttempt === $tries) {
                    return $exception instanceof RequestException && $throwOnMaxTries === false ? $exceptionResponse : throw $exception;
                }

                $nextRequest = clone $request;

                // Now we'll run the "handleRetry" method on both the connector and the request.
                // This method will return a boolean. If just one of the objects returns false
                // then we won't handle the retry.

                $handleRetry = $nextRequest->handleRetry($nextRequest, $exception, $exceptionResponse) && $this->handleRetry($nextRequest, $exception, $exceptionResponse);

                // If we cannot retry we will simply return the response or throw the exception
                // that we just caught in the last response.

                if ($handleRetry === false) {
                    return $exception instanceof RequestException && $throwOnMaxTries === false ? $exceptionResponse : throw $exception;
                }
            }
        }
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
     * @param callable(\Throwable, \Saloon\Contracts\Request): (bool)|null $handleRetry
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function sendAndRetry(Request $request, int $tries, int $interval = 0, callable $handleRetry = null, bool $throw = true, MockClient $mockClient = null): Response
    {
        $request->tries = $tries;
        $request->retryInterval = $interval;
        $request->throwOnMaxTries = $throw;
        $request->handleRetryCallable = $handleRetry;

        return $this->send($request, $mockClient);
    }

    /**
     * Create a new PendingRequest
     *
     * @throws \ReflectionException
     */
    public function createPendingRequest(Request $request, MockClient $mockClient = null): PendingRequestContract
    {
        return new PendingRequest($this, $request, $mockClient);
    }
}
