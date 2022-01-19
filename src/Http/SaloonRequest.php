<?php

namespace Sammyjo20\Saloon\Http;

use Sammyjo20\Saloon\Traits\CollectsData;
use Sammyjo20\Saloon\Traits\SendsRequests;
use Sammyjo20\Saloon\Traits\CollectsConfig;
use Sammyjo20\Saloon\Traits\CollectsHeaders;
use Sammyjo20\Saloon\Traits\CollectsHandlers;
use Sammyjo20\Saloon\Traits\CollectsQueryParams;
use Sammyjo20\Saloon\Traits\CollectsInterceptors;
use Sammyjo20\Saloon\Interfaces\SaloonRequestInterface;
use Sammyjo20\Saloon\Exceptions\SaloonMethodNotFoundException;
use Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException;

abstract class SaloonRequest implements SaloonRequestInterface
{
    use CollectsData,
        CollectsQueryParams,
        CollectsHeaders,
        CollectsConfig,
        CollectsHandlers,
        CollectsInterceptors,
        SendsRequests;

    /**
     * Define the method that the request will use.
     *
     * @var string|null
     */
    protected ?string $method = null;

    /**
     * The connector.
     *
     * @var string|null
     */
    protected ?string $connector = null;

    /**
     * The instantiated connector instance.
     *
     * @var SaloonConnector|null
     */
    private ?SaloonConnector $loadedConnector = null;

    /**
     * Define anything to be added to the request.
     *
     * @return void
     */
    public function boot(): void
    {
        // TODO: Implement boot() method.
    }

    /**
     * Get the method the class is using.
     *
     * @return string|null
     */
    public function getMethod(): ?string
    {
        if (empty($this->method)) {
            return null;
        }

        return $this->method;
    }

    /**
     * Boot the connector
     *
     * @return void
     * @throws SaloonInvalidConnectorException
     */
    private function bootConnector(): void
    {
        if (empty($this->connector) || ! class_exists($this->connector)) {
            throw new SaloonInvalidConnectorException;
        }

        $this->loadedConnector = new $this->connector;
    }

    /**
     * Get the connector instance. If it hasn't yet been booted, we will boot it up.
     *
     * @return SaloonConnector
     * @throws SaloonInvalidConnectorException
     */
    public function getConnector(): SaloonConnector
    {
        if (! $this->loadedConnector instanceof SaloonConnector) {
            $this->bootConnector();
        }

        return $this->loadedConnector;
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
        if (method_exists($this->getConnector(), $method) === false) {
            throw new SaloonMethodNotFoundException($method, $this->getConnector());
        }

        return $this->getConnector()->{$method}(...$parameters);
    }
}
