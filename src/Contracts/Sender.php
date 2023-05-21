<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use GuzzleHttp\Promise\PromiseInterface;
use Saloon\Data\FactoryCollection;

interface Sender
{
    /**
     * Get the factory collection
     *
     * @return FactoryCollection
     */
    public function getFactoryCollection(): FactoryCollection;

    /**
     * Send the request.
     *
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     * @param bool $asynchronous
     * @return ($asynchronous is true ? \Saloon\Contracts\Response|PromiseInterface : \Saloon\Contracts\Response)
     */
    public function sendRequest(PendingRequest $pendingRequest, bool $asynchronous = false): Response|PromiseInterface;
}
