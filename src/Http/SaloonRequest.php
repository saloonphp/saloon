<?php

namespace Sammyjo20\Saloon\Http;

use Sammyjo20\Saloon\Enums\Method;
use Sammyjo20\Saloon\Traits\BuildsUrls;
use Sammyjo20\Saloon\Traits\MocksRequests;
use Sammyjo20\Saloon\Traits\SendsRequests;
use Sammyjo20\Saloon\Traits\HasCustomResponses;
use Sammyjo20\Saloon\Traits\HasRequestProperties;
use Sammyjo20\Saloon\Traits\AuthenticatesRequests;
use Sammyjo20\Saloon\Traits\RetrievesRequestProperties;

abstract class SaloonRequest
{
    use HasRequestProperties;
    use RetrievesRequestProperties;
    use BuildsUrls;
    use HasCustomResponses;
    use MocksRequests;
    use AuthenticatesRequests;
    use SendsRequests;

    /**
     * @var string
     */
    protected string $connector = '';

    /**
     * @var SaloonConnector|null
     */
    private ?SaloonConnector $loadedConnector = null;

    /**
     * @var Method
     */
    protected Method $method = Method::GET;

    /**
     * Define the API endpoint used.
     *
     * @return string
     */
    abstract protected function defineEndpoint(): string;

    /**
     * @return Method
     */
    public function getMethod(): Method
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
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidResponseClassException
     */
    public function createPendingRequest(): PendingSaloonRequest
    {
        return new PendingSaloonRequest($this);
    }

    /**
     * @param PendingSaloonRequest $payload
     * @return void
     */
    public function boot(PendingSaloonRequest $payload): void
    {
        // Apply anything right before the request is sent.
    }
}
