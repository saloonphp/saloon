<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Senders;

use Saloon\Contracts\Sender;
use Saloon\Http\Responses\Response;
use Saloon\Contracts\PendingRequest;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Response as GuzzleResponse;

class ArraySender implements Sender
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
    public function sendRequest(PendingRequest $pendingRequest, bool $asynchronous = false): Response|PromiseInterface
    {
        /** @var class-string<\Saloon\Contracts\Response> $responseClass */
        $responseClass = $pendingRequest->getResponseClass();

        return $responseClass::fromPsrResponse(new GuzzleResponse(200, ['X-Fake' => true], 'Default'), $pendingRequest, null);
    }
}
