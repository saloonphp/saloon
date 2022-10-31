<?php

namespace Sammyjo20\Saloon\Http;

use Sammyjo20\Saloon\Traits\BuildsUrls;
use GuzzleHttp\Promise\PromiseInterface;
use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Traits\HasConnector;
use Sammyjo20\Saloon\Traits\MocksRequests;
use Sammyjo20\Saloon\Traits\CastsResponseToDto;
use Sammyjo20\Saloon\Traits\HasCustomResponses;
use Sammyjo20\Saloon\Exceptions\DataBagException;
use Sammyjo20\Saloon\Traits\HasRequestProperties;
use Sammyjo20\Saloon\Traits\AuthenticatesRequests;
use Sammyjo20\Saloon\Interfaces\SaloonResponseInterface;
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
     * Denotes if the request is being used to record a fixture.
     *
     * @var bool
     */
    protected bool $isRecordingFixture = false;

    /**
     * Define the endpoint for the request.
     *
     * @return string
     */
    abstract protected function defineEndpoint(): string;

    /**
     * Handle the booting of a request.
     *
     * @param PendingSaloonRequest $request
     * @return void
     */
    public function boot(PendingSaloonRequest $request): void
    {
        //
    }

    /**
     * Create the request payload which will run all plugins, boot methods, everything.
     *
     * @param MockClient|null $mockClient
     * @return PendingSaloonRequest
     * @throws \ReflectionException
     * @throws DataBagException
     * @throws PendingSaloonRequestException
     * @throws SaloonInvalidConnectorException
     * @throws SaloonInvalidResponseClassException
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
     * Set if the request is being used to record a fixture.
     *
     * @param bool $value
     * @return $this
     */
    public function setIsRecordingFixture(bool $value): static
    {
        $this->isRecordingFixture = $value;

        return $this;
    }

    /**
     * Get if the request is recording a fixture.
     *
     * @return bool
     */
    public function isRecordingFixture(): bool
    {
        return $this->isRecordingFixture;
    }

    /**
     * Get if the request is not recording a fixture.
     *
     * @return bool
     */
    public function isNotRecordingFixture(): bool
    {
        return ! $this->isRecordingFixture();
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

    // Todo: Move below into trait

    /**
     * Send a request
     *
     * @param MockClient|null $mockClient
     * @param bool $asynchronous
     * @return SaloonResponseInterface|PromiseInterface
     * @throws DataBagException
     * @throws PendingSaloonRequestException
     * @throws SaloonInvalidConnectorException
     * @throws SaloonInvalidResponseClassException
     * @throws \ReflectionException
     */
    public function send(MockClient $mockClient = null, bool $asynchronous = false): SaloonResponseInterface|PromiseInterface
    {
        return $this->connector()->send($this, $mockClient, $asynchronous);
    }

    /**
     * Send a request asynchronously
     *
     * @param MockClient|null $mockClient
     * @return PromiseInterface
     * @throws SaloonInvalidConnectorException
     * @throws SaloonInvalidResponseClassException
     * @throws \ReflectionException
     */
    public function sendAsync(MockClient $mockClient = null): PromiseInterface
    {
        return $this->send($mockClient, true);
    }
}
