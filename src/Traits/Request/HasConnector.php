<?php

declare(strict_types=1);

namespace Saloon\Traits\Request;

use Saloon\Http\Response;
use Saloon\Http\Connector;
use Saloon\Contracts\Sender;
use Saloon\Http\PendingRequest;
use Saloon\Http\Faking\MockClient;
use GuzzleHttp\Promise\PromiseInterface;

trait HasConnector
{
    /**
     * The loaded connector used in requests.
     */
    private ?Connector $loadedConnector = null;

    /**
     *  Retrieve the loaded connector.
     */
    public function connector(): Connector
    {
        return $this->loadedConnector ??= $this->resolveConnector();
    }

    /**
     * Set the loaded connector at runtime.
     *
     * @return $this
     */
    public function setConnector(Connector $connector): static
    {
        $this->loadedConnector = $connector;

        return $this;
    }

    /**
     * Create a new connector instance.
     */
    protected function resolveConnector(): Connector
    {
        return new $this->connector;
    }

    /**
     * Access the HTTP sender
     */
    public function sender(): Sender
    {
        return $this->connector()->sender();
    }

    /**
     * Create a pending request
     */
    public function createPendingRequest(?MockClient $mockClient = null): PendingRequest
    {
        return $this->connector()->createPendingRequest($this, $mockClient);
    }

    /**
     * Send a request synchronously
     */
    public function send(?MockClient $mockClient = null): Response
    {
        return $this->connector()->send($this, $mockClient);
    }

    /**
     * Send a request asynchronously
     */
    public function sendAsync(?MockClient $mockClient = null): PromiseInterface
    {
        return $this->connector()->sendAsync($this, $mockClient);
    }
}
