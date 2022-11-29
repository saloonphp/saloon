<?php

declare(strict_types=1);

namespace Saloon\Http;

use Saloon\Traits\Bootable;
use Saloon\Traits\Makeable;
use Saloon\Contracts\Sender;
use Saloon\Contracts\Response;
use Saloon\Contracts\MockClient;
use Saloon\Traits\Conditionable;
use Saloon\Traits\HasMockClient;
use Saloon\Traits\Request\BuildsUrls;
use Saloon\Traits\Request\HasConnector;
use GuzzleHttp\Promise\PromiseInterface;
use Saloon\Traits\Auth\AuthenticatesRequests;
use Saloon\Traits\Request\CastDtoFromResponse;
use Saloon\Traits\Responses\HasCustomResponses;
use Saloon\Contracts\Request as RequestContract;
use Saloon\Traits\RequestProperties\HasRequestProperties;

abstract class Request implements RequestContract
{
    use AuthenticatesRequests;
    use HasRequestProperties;
    use CastDtoFromResponse;
    use HasCustomResponses;
    use HasMockClient;
    use Conditionable;
    use HasConnector;
    use BuildsUrls;
    use Bootable;
    use Makeable;

    /**
     * Define the connector.
     *
     * @var string
     */
    protected string $connector = '';

    /**
     * Define the HTTP method.
     *
     * @var string
     */
    protected string $method = '';

    /**
     * Define the endpoint for the request.
     *
     * @return string
     */
    abstract protected function defineEndpoint(): string;

    /**
     * Create a pending request
     *
     * @param \Saloon\Contracts\MockClient|null $mockClient
     * @return \Saloon\Http\PendingRequest<static>
     * @throws \ReflectionException
     * @throws \Saloon\Exceptions\PendingRequestException
     */
    public function createPendingRequest(MockClient $mockClient = null): PendingRequest
    {
        return new PendingRequest($this, $mockClient);
    }

    /**
     * Access the HTTP sender
     *
     * @return \Saloon\Contracts\Sender
     * @throws \Saloon\Exceptions\InvalidConnectorException
     */
    public function sender(): Sender
    {
        return $this->connector()->sender();
    }

    /**
     * Send a request synchronously
     *
     * @param \Saloon\Contracts\MockClient|null $mockClient
     * @return \Saloon\Contracts\Response
     * @throws \Saloon\Exceptions\InvalidConnectorException
     */
    public function send(MockClient $mockClient = null): Response
    {
        return $this->connector()->send($this, $mockClient);
    }

    /**
     * Send a request asynchronously
     *
     * @param \Saloon\Contracts\MockClient|null $mockClient
     * @return \GuzzleHttp\Promise\PromiseInterface
     * @throws \Saloon\Exceptions\InvalidConnectorException
     */
    public function sendAsync(MockClient $mockClient = null): PromiseInterface
    {
        return $this->connector()->sendAsync($this, $mockClient);
    }

    /**
     * Get the method of the request.
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }
}
