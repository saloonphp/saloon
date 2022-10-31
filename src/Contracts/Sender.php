<?php

namespace Sammyjo20\Saloon\Contracts;

use GuzzleHttp\Promise\PromiseInterface;
use Sammyjo20\Saloon\Http\PendingSaloonRequest;
use Sammyjo20\Saloon\Http\Responses\SaloonResponse;

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
     * @param PendingSaloonRequest $pendingRequest
     * @param bool $asynchronous
     * @return SaloonResponse|PromiseInterface
     */
    public function sendRequest(PendingSaloonRequest $pendingRequest, bool $asynchronous = false): SaloonResponse|PromiseInterface;
}
