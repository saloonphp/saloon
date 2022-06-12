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
     * @param PendingSaloonRequest $saloonRequest
     * @param bool $asynchronous
     * @return SaloonResponse|PromiseInterface
     */
    public function processRequest(PendingSaloonRequest $saloonRequest, bool $asynchronous = false): SaloonResponse|PromiseInterface;

    /**
     * Process the response
     *
     * @param PendingSaloonRequest $saloonRequest
     * @param SaloonResponse $saloonResponse
     * @param bool $asPromise
     * @return SaloonResponse|PromiseInterface
     */
    public function processResponse(PendingSaloonRequest $saloonRequest, SaloonResponse $saloonResponse, bool $asPromise = false): SaloonResponse|PromiseInterface;

    /**
     * Return the base response class used to validate the custom response.
     *
     * @return string
     */
    public function getBaseResponseClass(): string;
}
