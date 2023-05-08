<?php

declare(strict_types=1);

namespace Saloon\Traits\Connector;

use LogicException;
use Saloon\Contracts\Request;
use Saloon\Contracts\Response;
use Saloon\Http\PendingRequest;
use Saloon\Contracts\MockClient;
use GuzzleHttp\Promise\PromiseInterface;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Contracts\PendingRequest as PendingRequestContract;
use Saloon\Http\Senders\SimulatedSender;

trait SendsRequests
{
    /**
     * Send a request
     *
     * @param \Saloon\Contracts\Request $request
     * @param \Saloon\Contracts\MockClient|null $mockClient
     * @return \Saloon\Contracts\Response
     * @throws \ReflectionException
     * @throws \Saloon\Exceptions\InvalidResponseClassException
     * @throws \Saloon\Exceptions\PendingRequestException
     * @throws \Saloon\Exceptions\SenderException
     * @throws \Throwable
     */
    public function send(Request $request, MockClient $mockClient = null): Response
    {
        $pendingRequest = $this->createPendingRequest($request, $mockClient);

        $sender = $pendingRequest->hasSimulatedResponsePayload() ? new SimulatedSender : $this->sender();

        $response = $sender->sendRequest($pendingRequest);

        return $pendingRequest->executeResponsePipeline($response);
    }

    /**
     * Send a request asynchronously
     *
     * @param \Saloon\Contracts\Request $request
     * @param \Saloon\Contracts\MockClient|null $mockClient
     * @return \GuzzleHttp\Promise\PromiseInterface
     * @throws \ReflectionException
     * @throws \Saloon\Exceptions\InvalidResponseClassException
     * @throws \Saloon\Exceptions\PendingRequestException
     * @throws \Saloon\Exceptions\SenderException
     * @throws \Throwable
     */
    public function sendAsync(Request $request, MockClient $mockClient = null): PromiseInterface
    {
        $pendingRequest = $this->createPendingRequest($request, $mockClient)->setAsynchronous(true);

        $sender = $pendingRequest->hasSimulatedResponsePayload() ? new SimulatedSender : $this->sender();

        $promise = $sender->sendRequest($pendingRequest, $pendingRequest->isAsynchronous());

        return $promise->then(fn (Response $response) => $pendingRequest->executeResponsePipeline($response));
    }

    /**
     * Send a synchronous request and retry if it fails
     *
     * @param \Saloon\Contracts\Request $request
     * @param int $maxAttempts
     * @param int $interval
     * @param callable(\Throwable, \Saloon\Contracts\Request): (bool)|null $handleRetry
     * @param bool $throw
     * @param \Saloon\Contracts\MockClient|null $mockClient
     * @return \Saloon\Contracts\Response
     * @throws \ReflectionException
     * @throws \Saloon\Exceptions\InvalidResponseClassException
     * @throws \Saloon\Exceptions\PendingRequestException
     * @throws \Saloon\Exceptions\Request\FatalRequestException
     * @throws \Saloon\Exceptions\Request\RequestException
     * @throws \Saloon\Exceptions\SenderException
     * @throws \Throwable
     */
    public function sendAndRetry(Request $request, int $maxAttempts, int $interval = 0, callable $handleRetry = null, bool $throw = true, MockClient $mockClient = null): Response
    {
        $currentAttempt = 0;
        $currentRequest = clone $request;

        if ($mockClient instanceof MockClient) {
            $currentRequest->withMockClient($mockClient);
        }

        while ($currentAttempt < $maxAttempts) {
            $currentAttempt++;

            // When the current attempt is greater than one, we will pause to wait
            // for the interval.

            if ($currentAttempt > 1) {
                usleep($interval * 1000);
            }

            try {
                // We'll attempt to send the PendingRequest. We'll also use the throw
                // method which will throw an exception if the request has failed.

                return $this->send($currentRequest)->throw();
            } catch (FatalRequestException|RequestException $exception) {
                // We won't create another pending request if our current attempt is
                // the max attempts we can make

                if ($currentAttempt === $maxAttempts) {
                    return $exception instanceof RequestException && $throw === false ? $exception->getResponse() : throw $exception;
                }

                $currentRequest = clone $request;

                // When either the FatalRequestException happens or the RequestException
                // happens, we should catch it and check if we should retry. If someone
                // has provided a callable into $handleRetry, we'll wait for the result
                // of the callable to retry.

                if (is_null($handleRetry) || $handleRetry($exception, $currentRequest) === true) {
                    continue;
                }

                // If we should not retry, we need to return the last response. If the
                // exception was a RequestException, we should return the response,
                // otherwise we'll throw the exception.

                return $exception instanceof RequestException && $throw === false ? $exception->getResponse() : throw $exception;
            }
        }

        throw new LogicException('Maximum number of attempts has been reached.');
    }

    /**
     * Create a new PendingRequest
     *
     * @param \Saloon\Contracts\Request $request
     * @param \Saloon\Contracts\MockClient|null $mockClient
     * @return \Saloon\Contracts\PendingRequest
     * @throws \ReflectionException
     * @throws \Saloon\Exceptions\InvalidResponseClassException
     * @throws \Saloon\Exceptions\PendingRequestException
     */
    public function createPendingRequest(Request $request, MockClient $mockClient = null): PendingRequestContract
    {
        return new PendingRequest($this, $request, $mockClient);
    }
}
