<?php declare(strict_types=1);

namespace Saloon\Http;

use Saloon\Traits\Bootable;
use Saloon\Contracts\Sender;
use Saloon\Contracts\Response;
use Saloon\Contracts\MockClient;
use Saloon\Traits\HasMockClient;
use Saloon\Traits\Request\BuildsUrls;
use Saloon\Traits\Request\HasConnector;
use GuzzleHttp\Promise\PromiseInterface;
use Saloon\Traits\Auth\AuthenticatesRequests;
use Saloon\Traits\Request\CastDtoFromResponse;
use Saloon\Traits\RequestProperties\HasMergeOptions;
use Saloon\Traits\Responses\HasCustomResponses;
use Saloon\Exceptions\PendingRequestException;
use Saloon\Exceptions\InvalidConnectorException;
use Saloon\Traits\RequestProperties\HasRequestProperties;
use Saloon\Exceptions\InvalidResponseClassException;

abstract class Request
{
    use AuthenticatesRequests;
    use HasRequestProperties;
    use CastDtoFromResponse;
    use HasCustomResponses;
    use HasMergeOptions;
    use HasMockClient;
    use HasConnector;
    use BuildsUrls;
    use Bootable;

    /**
     * Define the connector.
     *
     * @var string
     */
    protected string $connector = '';

    /**
     * Define the method.
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
     * @param MockClient|null $mockClient
     * @return PendingRequest
     * @throws PendingRequestException
     * @throws InvalidConnectorException
     * @throws InvalidResponseClassException
     * @throws \ReflectionException
     */
    public function createPendingRequest(MockClient $mockClient = null): PendingRequest
    {
        return new PendingRequest($this, $mockClient);
    }

    /**
     * Access the HTTP sender
     *
     * @return Sender
     * @throws InvalidConnectorException
     */
    public function sender(): Sender
    {
        return $this->connector()->sender();
    }

    /**
     * Send a request synchronously
     *
     * @param MockClient|null $mockClient
     * @return Response
     * @throws \ReflectionException
     * @throws \Saloon\Exceptions\SaloonException
     */
    public function send(MockClient $mockClient = null): Response
    {
        return $this->connector()->send($this, $mockClient);
    }

    /**
     * Send a request asynchronously
     *
     * @param MockClient|null $mockClient
     * @return PromiseInterface
     * @throws \ReflectionException
     * @throws \Saloon\Exceptions\SaloonException
     */
    public function sendAsync(MockClient $mockClient = null): PromiseInterface
    {
        return $this->connector()->sendAsync($this, $mockClient);
    }

    /**
     * Instantiate a new class with the arguments.
     *
     * @param ...$arguments
     * @return static
     */
    public static function make(...$arguments): static
    {
        return new static(...$arguments);
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
