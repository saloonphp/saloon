<?php

namespace Sammyjo20\Saloon\Http;

use Sammyjo20\Saloon\Traits\Bootable;
use Sammyjo20\Saloon\Traits\BuildsUrls;
use GuzzleHttp\Promise\PromiseInterface;
use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Traits\HasConnector;
use Sammyjo20\Saloon\Traits\MocksRequests;
use Sammyjo20\Saloon\Traits\RecordsFixtures;
use Sammyjo20\Saloon\Contracts\SaloonResponse;
use Sammyjo20\Saloon\Traits\CastsResponseToDto;
use Sammyjo20\Saloon\Traits\HasCustomResponses;
use Sammyjo20\Saloon\Traits\HasRequestProperties;
use Sammyjo20\Saloon\Traits\AuthenticatesRequests;
use Sammyjo20\Saloon\Exceptions\PendingSaloonRequestException;
use Sammyjo20\Saloon\Exceptions\SaloonMethodNotFoundException;
use Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException;
use Sammyjo20\Saloon\Exceptions\SaloonInvalidResponseClassException;

abstract class SaloonRequest
{
    use HasRequestProperties;
    use AuthenticatesRequests;
    use HasCustomResponses;
    use MocksRequests;
    use BuildsUrls;
    use CastsResponseToDto;
    use HasConnector;
    use Bootable;
    use RecordsFixtures;

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
     * @return PendingSaloonRequest
     * @throws PendingSaloonRequestException
     * @throws SaloonInvalidConnectorException
     * @throws SaloonInvalidResponseClassException
     * @throws \ReflectionException
     */
    public function createPendingRequest(MockClient $mockClient = null): PendingSaloonRequest
    {
        return new PendingSaloonRequest($this, $mockClient);
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

    /**
     * Send a request
     *
     * @param MockClient|null $mockClient
     * @param bool $asynchronous
     * @return SaloonResponse|PromiseInterface
     * @throws SaloonInvalidConnectorException
     * @throws \ReflectionException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonException
     */
    public function send(MockClient $mockClient = null, bool $asynchronous = false): SaloonResponse|PromiseInterface
    {
        return $this->connector()->send($this, $mockClient, $asynchronous);
    }

    /**
     * Send a request asynchronously
     *
     * @param MockClient|null $mockClient
     * @return PromiseInterface
     * @throws SaloonInvalidConnectorException
     * @throws \ReflectionException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonException
     */
    public function sendAsync(MockClient $mockClient = null): PromiseInterface
    {
        return $this->send($mockClient, true);
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
     * Dynamically proxy other methods to the connector.
     *
     * @param $method
     * @param $parameters
     * @return mixed
     * @throws SaloonInvalidConnectorException
     * @throws SaloonMethodNotFoundException
     */
    public function __call($method, $parameters)
    {
        $connector = $this->connector();

        if (method_exists($connector, $method) === false) {
            throw new SaloonMethodNotFoundException($method, $connector);
        }

        return $connector->{$method}(...$parameters);
    }
}
