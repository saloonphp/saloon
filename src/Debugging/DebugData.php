<?php

declare(strict_types=1);

namespace Saloon\Debugging;

use Saloon\Contracts\Connector;
use Saloon\Contracts\PendingRequest;
use Saloon\Contracts\Request;
use Saloon\Contracts\Response;
use Saloon\Contracts\Sender;
use Saloon\Enums\Method;

class DebugData
{
    public function __construct(
        protected readonly PendingRequest $pendingRequest,
        protected readonly ?Response $response,
    ) {}

    public function wasSent(): bool
    {
        return ! is_null($this->response);
    }

    public function wasNotSent(): bool
    {
        return ! $this->wasSent();
    }

    public function connector(): Connector
    {
        return $this->pendingRequest->getConnector();
    }

    public function sender(): Sender
    {
        return $this->pendingRequest->getSender();
    }

    public function pendingRequest(): PendingRequest
    {
        return $this->pendingRequest;
    }

    public function request(): Request
    {
        return $this->pendingRequest->getRequest();
    }

    public function url(): string
    {
        return $this->pendingRequest->getUrl();
    }

    public function method(): Method
    {
        return $this->pendingRequest->getMethod();
    }

    public function response(): ?Response
    {
        return $this->response;
    }

    public function statusCode(): ?int
    {
        return $this->response?->status();
    }
}
