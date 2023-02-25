<?php

declare(strict_types=1);

namespace Saloon\Debugging;

use Saloon\Enums\Method;
use Saloon\Contracts\Sender;
use Saloon\Contracts\Request;
use Saloon\Contracts\Response;
use Saloon\Contracts\Connector;
use Saloon\Contracts\PendingRequest;

class DebugData
{
    /**
     * Constructor
     *
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     * @param \Saloon\Contracts\Response|null $response
     */
    public function __construct(
        protected readonly PendingRequest $pendingRequest,
        protected readonly ?Response $response,
    ) {
        //
    }

    /**
     * Denotes if the request was sent
     *
     * @return bool
     */
    public function wasSent(): bool
    {
        return ! is_null($this->response);
    }

    /**
     * Denotes if the request was not sent
     *
     * @return bool
     */
    public function wasNotSent(): bool
    {
        return ! $this->wasSent();
    }

    /**
     * Get the connector from the PendingRequest
     *
     * @return \Saloon\Contracts\Connector
     */
    public function getConnector(): Connector
    {
        return $this->pendingRequest->getConnector();
    }

    /**
     * Get the sender from the PendingRequest
     *
     * @return \Saloon\Contracts\Sender
     */
    public function getSender(): Sender
    {
        return $this->pendingRequest->getSender();
    }

    /**
     * Get the PendingRequest
     *
     * @return \Saloon\Contracts\PendingRequest
     */
    public function getPendingRequest(): PendingRequest
    {
        return $this->pendingRequest;
    }

    /**
     * Get the request from the PendingRequest
     *
     * @return \Saloon\Contracts\Request
     */
    public function getRequest(): Request
    {
        return $this->pendingRequest->getRequest();
    }

    /**
     * Get the URL from the PendingRequest
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->pendingRequest->getUrl();
    }

    /**
     * Get the method from the PendingRequest
     *
     * @return \Saloon\Enums\Method
     */
    public function getMethod(): Method
    {
        return $this->pendingRequest->getMethod();
    }

    /**
     * Get the response
     *
     * @return \Saloon\Contracts\Response|null
     */
    public function getResponse(): ?Response
    {
        return $this->response;
    }

    /**
     * Get the status code from the response
     *
     * @return int|null
     */
    public function getStatusCode(): ?int
    {
        return $this->response?->status();
    }
}
