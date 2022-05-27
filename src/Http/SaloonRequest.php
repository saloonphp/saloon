<?php

namespace Sammyjo20\Saloon\Http;

use Sammyjo20\Saloon\Enums\Method;
use Sammyjo20\Saloon\Interfaces\SaloonRequestInterface;
use Sammyjo20\Saloon\Traits\BuildsUrls;
use Sammyjo20\Saloon\Traits\HasCustomResponses;
use Sammyjo20\Saloon\Traits\HasRequestProperties;
use Sammyjo20\Saloon\Traits\MocksRequests;

abstract class SaloonRequest implements SaloonRequestInterface
{
    use HasRequestProperties;
    use BuildsUrls;
    use HasCustomResponses;
    use MocksRequests;

    /**
     * @var string|null
     */
    protected ?string $connector = null;

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
     * @return RequestPayload
     * @throws \ReflectionException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidResponseClassException
     */
    public function createRequestPayload(): RequestPayload
    {
        return new RequestPayload($this);
    }

    /**
     * @param RequestPayload $payload
     * @return void
     */
    public function beforeSend(RequestPayload $payload): void
    {
        // Apply anything right before the request is sent.
    }
}
