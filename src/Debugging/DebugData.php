<?php

declare(strict_types=1);

namespace Saloon\Debugging;

use Saloon\Enums\Method;
use Saloon\Contracts\Request;
use Saloon\Contracts\Response;
use Saloon\Contracts\Connector;
use Saloon\Contracts\PendingRequest;

readonly class DebugData
{
    /**
     * Constructor
     */
    public function __construct(
        protected PendingRequest $pendingRequest,
        protected ?Response      $response,
    ) {
        //
    }

    /**
     * Denotes if the request was sent
     */
    public function wasSent(): bool
    {
        return ! is_null($this->response);
    }

    /**
     * Denotes if the request was not sent
     */
    public function wasNotSent(): bool
    {
        return ! $this->wasSent();
    }

    /**
     * Get the PendingRequest
     */
    public function getPendingRequest(): PendingRequest
    {
        return $this->pendingRequest;
    }

    /**
     * Get the connector from the PendingRequest
     */
    public function getConnector(): Connector
    {
        return $this->pendingRequest->getConnector();
    }

    /**
     * Get the request from the PendingRequest
     */
    public function getRequest(): Request
    {
        return $this->pendingRequest->getRequest();
    }

    /**
     * Get the URL from the PendingRequest
     */
    public function getUrl(): string
    {
        return $this->pendingRequest->getUrl();
    }

    /**
     * Get the method from the PendingRequest
     */
    public function getMethod(): Method
    {
        return $this->pendingRequest->getMethod();
    }

    /**
     * Get the response
     */
    public function getResponse(): ?Response
    {
        return $this->response;
    }

    /**
     * Get the status code from the response
     */
    public function getStatusCode(): ?int
    {
        return $this->response?->status();
    }
}
