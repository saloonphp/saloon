<?php

namespace Sammyjo20\Saloon\Interfaces;

use GuzzleHttp\Promise\PromiseInterface;
use Sammyjo20\Saloon\Http\PendingSaloonRequest;
use Sammyjo20\Saloon\Http\Responses\SaloonResponse;

interface SenderInterface
{
    /**
     * Send the request.
     *
     * @param PendingSaloonRequest $pendingRequest
     * @param bool $asynchronous
     * @return SaloonResponse|PromiseInterface
     */
    public function sendRequest(PendingSaloonRequest $pendingRequest, bool $asynchronous = false): SaloonResponseInterface|PromiseInterface;

    /**
     * Return the base response class used to validate the custom response.
     *
     * @return string
     */
    public function getResponseClass(): string;
}
