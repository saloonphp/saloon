<?php

namespace Sammyjo20\Saloon\Interfaces;

use GuzzleHttp\Promise\PromiseInterface;
use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\Saloon\Http\PendingSaloonRequest;
use Sammyjo20\Saloon\Http\Responses\SaloonResponse;

interface RequestSenderInterface
{
    /**
     * Send the request.
     *
     * @param PendingSaloonRequest $pendingRequest
     * @param bool $asynchronous
     * @return SaloonResponse|PromiseInterface
     */
    public function processRequest(PendingSaloonRequest $pendingRequest, bool $asynchronous = false): SaloonResponse|PromiseInterface;

    /**
     * Process the response
     *
     * @param PendingSaloonRequest $pendingRequest
     * @param SaloonResponse $saloonResponse
     * @param bool $asPromise
     * @return SaloonResponse|PromiseInterface
     */
    public function processResponse(PendingSaloonRequest $pendingRequest, SaloonResponse $saloonResponse, bool $asPromise = false): SaloonResponse|PromiseInterface;

    /**
     * Return the base response class used to validate the custom response.
     *
     * @return string
     */
    public function getBaseResponseClass(): string;
}
