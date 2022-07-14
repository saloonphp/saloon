<?php

namespace Sammyjo20\Saloon\Http;

use Sammyjo20\Saloon\Exceptions\SaloonMethodNotFoundException;
use Sammyjo20\Saloon\Traits\BuildsUrls;
use Sammyjo20\Saloon\Traits\CastsResponseToDto;
use Sammyjo20\Saloon\Traits\MocksRequests;
use Sammyjo20\Saloon\Traits\SendsRequests;
use Sammyjo20\Saloon\Traits\HasCustomResponses;
use Sammyjo20\Saloon\Traits\HasRequestProperties;
use Sammyjo20\Saloon\Traits\AuthenticatesRequests;
use Sammyjo20\Saloon\Traits\BundlesRequestProperties;

/**
 * @method Sender sender
 */
abstract class SaloonRequest
{
    use HasRequestProperties;
    use BundlesRequestProperties;
    use AuthenticatesRequests;
    use HasCustomResponses;
    use MocksRequests;
    use SendsRequests;
    use BuildsUrls;
    use CastsResponseToDto;

    /**
     * @var string
     */
    protected string $connector = '';

    /**
     * @var string
     */
    protected string $method = '';

    /**
     * @var SaloonConnector|null
     */
    private ?SaloonConnector $loadedConnector = null;

    /**
     * Define the API endpoint used.
     *
     * @return string
     */
    abstract protected function defineEndpoint(): string;

    /**
     * @param PendingSaloonRequest $payload
     * @return void
     */
    public function boot(PendingSaloonRequest $payload): void
    {
        // Apply anything right before the request is sent.
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
     * Retrieve the loaded connector.
     *
     * @return SaloonConnector
     */
    public function getConnector(): SaloonConnector
    {
        return $this->loadedConnector ??= new $this->connector;
    }

    /**
     * Set the loaded connector at runtime.
     *
     * @param SaloonConnector $connector
     * @return $this
     */
    public function setConnector(SaloonConnector $connector): self
    {
        $this->loadedConnector = $connector;

        return $this;
    }

    /**
     * Create the request payload which will run all plugins, boot methods, everything.
     *
     * @return PendingSaloonRequest
     * @throws \ReflectionException
     * @throws \Sammyjo20\Saloon\Exceptions\DataBagException
     * @throws \Sammyjo20\Saloon\Exceptions\PendingSaloonRequestException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidResponseClassException
     */
    public function createPendingRequest(): PendingSaloonRequest
    {
        return new PendingSaloonRequest($this);
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
     * @throws SaloonMethodNotFoundException
     */
    public function __call($method, $parameters)
    {
        $connector = $this->getConnector();

        if (method_exists($connector, $method) === false) {
            throw new SaloonMethodNotFoundException($method, $connector);
        }

        return $connector->{$method}(...$parameters);
    }
}
