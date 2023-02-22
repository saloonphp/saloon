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
    public function __construct(
        protected readonly PendingRequest $pendingRequest,
        protected readonly ?Response $response,
    ) {
    }

    public function wasSent(): bool
    {
        return ! is_null($this->response);
    }

    public function wasNotSent(): bool
    {
        return ! $this->wasSent();
    }

    public function getConnector(): Connector
    {
        return $this->pendingRequest->getConnector();
    }

    public function getSender(): Sender
    {
        return $this->pendingRequest->getSender();
    }

    public function getPendingRequest(): PendingRequest
    {
        return $this->pendingRequest;
    }

    public function getRequest(): Request
    {
        return $this->pendingRequest->getRequest();
    }

    public function getUrl(): string
    {
        return $this->pendingRequest->getUrl();
    }

    public function getMethod(): Method
    {
        return $this->pendingRequest->getMethod();
    }

    public function getResponse(): ?Response
    {
        return $this->response;
    }

    public function getStatusCode(): ?int
    {
        return $this->response?->status();
    }
}
