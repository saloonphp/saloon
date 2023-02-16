<?php

declare(strict_types=1);

namespace Saloon\Http\Senders;

use Throwable;
use Saloon\Contracts\Sender;
use Saloon\Contracts\PendingRequest;
use Saloon\Http\Faking\MockResponse;
use Saloon\Exceptions\SenderException;
use GuzzleHttp\Promise\RejectedPromise;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use Saloon\Contracts\SimulatedResponsePayload;
use Saloon\Contracts\Response as ResponseContract;

class SimulatedSender implements Sender
{
    /**
     * Send the request.
     *
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     * @param bool $asynchronous
     * @return \Saloon\Contracts\Response|\GuzzleHttp\Promise\PromiseInterface
     * @throws \Saloon\Exceptions\SenderException
     * @throws \Throwable
     */
    public function sendRequest(PendingRequest $pendingRequest, bool $asynchronous = false): ResponseContract|PromiseInterface
    {
        $simulatedResponsePayload = $pendingRequest->getSimulatedResponsePayload();

        if (! $simulatedResponsePayload instanceof SimulatedResponsePayload) {
            throw new SenderException('Simulated response payload must be present on the pending request instance');
        }

        // Check if the SimulatedResponsePayload throws an exception. If the request is
        // asynchronous, then we should allow the promise handler to deal with the exception.

        $exception = $simulatedResponsePayload->getException($pendingRequest);

        if ($exception instanceof Throwable && $asynchronous === false) {
            throw $exception;
        }

        // Let's create our response!

        /** @var class-string<\Saloon\Contracts\Response> $responseClass */
        $responseClass = $pendingRequest->getResponseClass();

        $response = $responseClass::fromPsrResponse(
            psrResponse: $simulatedResponsePayload->getPsrResponse(),
            pendingRequest: $pendingRequest,
            senderException: $exception
        );

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

        if ($pendingRequest->delay()->isNotEmpty()) {
            usleep($pendingRequest->delay()->get() * 1000);
        }

        if ($asynchronous === false) {
            return $response;
        }

        // When mocking asynchronous requests we need to wrap the response
        // in FulfilledPromise or RejectedPromise depending on if the
        // response has an exception.

        $exception ??= $response->toException();

        return $exception instanceof Throwable ? new RejectedPromise($exception) : new FulfilledPromise($response);
    }
}
