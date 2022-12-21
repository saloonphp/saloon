<?php

declare(strict_types=1);

namespace Saloon\Http\Senders;

use Throwable;
use Saloon\Http\Response;
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
     * @param PendingRequest $pendingRequest
     * @param bool $asynchronous
     * @return Response|PromiseInterface
     * @throws \Saloon\Exceptions\SenderException
     */
    public function sendRequest(PendingRequest $pendingRequest, bool $asynchronous = false): ResponseContract|PromiseInterface
    {
        $simulatedResponsePayload = $pendingRequest->getSimulatedResponsePayload();

        if (! $simulatedResponsePayload instanceof SimulatedResponsePayload) {
            throw new SenderException('Simulated response payload must be present on the pending request instance');
        }

        // Let's create our response!

        $exception = $simulatedResponsePayload->getException($pendingRequest);

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
