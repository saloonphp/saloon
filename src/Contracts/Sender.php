<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use Saloon\Data\FactoryCollection;
use GuzzleHttp\Promise\PromiseInterface;

interface Sender
{
    /**
     * Get the factory collection
     *
     * @return FactoryCollection
     */
    public function getFactoryCollection(): FactoryCollection;

    /**
     * Send the request synchronously
     *
     * @param PendingRequest $pendingRequest
     * @return Response
     */
    public function send(PendingRequest $pendingRequest): Response;

    /**
     * Send the request asynchronously
     *
     * @param PendingRequest $pendingRequest
     * @return PromiseInterface
     */
    public function sendAsync(PendingRequest $pendingRequest): PromiseInterface;
}
