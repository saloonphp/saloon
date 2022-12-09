<?php

declare(strict_types=1);

namespace Saloon\Http\Senders;

use Throwable;
use Saloon\Contracts\Sender;
use Saloon\Http\Responses\Response;
use Saloon\Contracts\PendingRequest;
use Saloon\Http\Faking\MockResponse;
use GuzzleHttp\Promise\RejectedPromise;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use Saloon\Contracts\Response as ResponseContract;

class SimulatedSender implements Sender
{
    /**
     * Get the sender's response class
     *
     * @return string
     */
    public function getResponseClass(): string
    {
        return Response::class;
    }

    /**
     * Send the request.
     *
     * @param PendingRequest $pendingRequest
     * @param bool $asynchronous
     * @return Response|PromiseInterface
     */
    public function sendRequest(PendingRequest $pendingRequest, bool $asynchronous = false): ResponseContract|PromiseInterface
    {
        $simulatedResponsePayload = $pendingRequest->getSimulatedResponsePayload();
        $exception = $simulatedResponsePayload?->getException($pendingRequest);

        // When the pending request instance has SimulatedResponsePayload it means
        // we shouldn't send a real request. We can convert the payload into
        // a PSR-compatible ResponseInterface class which means we can use
        // can also make use of custom responses.

        $responseClass = $pendingRequest->getResponseClass();

        /** @var \Saloon\Contracts\Response $response */
        $response = new $responseClass($pendingRequest, $simulatedResponsePayload, $exception);

        // When the SimulatedResponsePayload is specifically a MockResponse then
        // we will record the response, and we'll set the "isMocked" property
        // on the response.

        if ($simulatedResponsePayload instanceof MockResponse) {
            $pendingRequest->getMockClient()?->recordResponse($response);
            $response->setMocked(true);
        }

        // We'll also set the SimulatedResponsePayload on the response
        // for people to access it if they need to.

        $response->setSimulatedResponsePayload($simulatedResponsePayload);

        // We'll return the synchronous response directly

        if ($asynchronous === false) {
            return $response;
        }

        // When mocking asynchronous requests we need to wrap the response
        // in FulfilledPromise to act like a real response.

        $exception ??= $response->toException();

        return $exception instanceof Throwable ? new RejectedPromise($exception) : new FulfilledPromise($response);
    }
}
