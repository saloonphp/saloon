<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use Saloon\Http\Response;
use Saloon\Http\PendingRequest;
use Saloon\Data\FactoryCollection;
use GuzzleHttp\Promise\PromiseInterface;

interface Sender
{
    /**
     * Get the factory collection
     */
    public function getFactoryCollection(): FactoryCollection;

    /**
     * Send the request synchronously
     */
    public function send(PendingRequest $pendingRequest): Response;

    /**
     * Send the request asynchronously
     */
    public function sendAsync(PendingRequest $pendingRequest): PromiseInterface;
}
