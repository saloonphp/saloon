<?php declare(strict_types=1);

namespace Sammyjo20\Saloon\Http;

use Sammyjo20\Saloon\Traits\Bootable;
use Sammyjo20\Saloon\Contracts\Sender;
use GuzzleHttp\Promise\PromiseInterface;
use Sammyjo20\Saloon\Contracts\MockClient;
use Sammyjo20\Saloon\Traits\MocksRequests;
use Sammyjo20\Saloon\Contracts\SaloonResponse;
use Sammyjo20\Saloon\Traits\HasCustomResponses;
use Sammyjo20\Saloon\Traits\Request\BuildsUrls;
use Sammyjo20\Saloon\Traits\Request\HasConnector;
use Sammyjo20\Saloon\Traits\Auth\AuthenticatesRequests;
use Sammyjo20\Saloon\Traits\Request\CastDtoFromResponse;
use Sammyjo20\Saloon\Exceptions\PendingSaloonRequestException;
use Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException;
use Sammyjo20\Saloon\Traits\RequestProperties\HasRequestProperties;
use Sammyjo20\Saloon\Exceptions\SaloonInvalidResponseClassException;

abstract class SaloonRequest
{
    use AuthenticatesRequests;
    use HasRequestProperties;
    use CastDtoFromResponse;
    use HasCustomResponses;
    use MocksRequests;
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
     * Access the HTTP sender
     *
     * @return Sender
     * @throws SaloonInvalidConnectorException
     */
    public function sender(): Sender
    {
        return $this->connector()->sender();
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
     * Get the method of the request.
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }
}
