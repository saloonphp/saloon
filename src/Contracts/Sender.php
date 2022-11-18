<?php declare(strict_types=1);

namespace Saloon\Contracts;

use GuzzleHttp\Promise\PromiseInterface;

interface Sender
{
    /**
     * Get the sender's response class
     *
     * @return string
     */
    public function getResponseClass(): string;

    /**
     * Send the request.
     *
     * @param PendingRequest $pendingRequest
     * @param bool $asynchronous
     * @return Response|PromiseInterface
     */
    public function sendRequest(PendingRequest $pendingRequest, bool $asynchronous = false): Response|PromiseInterface;
}
