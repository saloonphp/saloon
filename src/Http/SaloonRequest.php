<?php

namespace Sammyjo20\Saloon\Http;

use ReflectionClass;
use Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException;
use Sammyjo20\Saloon\Exceptions\SaloonMethodNotFoundException;
use Sammyjo20\Saloon\Interfaces\SaloonRequestInterface;
use Sammyjo20\Saloon\Traits\CollectsConfig;
use Sammyjo20\Saloon\Traits\CollectsData;
use Sammyjo20\Saloon\Traits\CollectsHandlers;
use Sammyjo20\Saloon\Traits\CollectsHeaders;
use Sammyjo20\Saloon\Traits\CollectsInterceptors;
use Sammyjo20\Saloon\Traits\CollectsQueryParams;
use Sammyjo20\Saloon\Traits\HasCustomResponses;
use Sammyjo20\Saloon\Traits\SendsRequests;

abstract class SaloonRequest implements SaloonRequestInterface
{
    use CollectsData,
        CollectsQueryParams,
        CollectsHeaders,
        CollectsConfig,
        CollectsHandlers,
        CollectsInterceptors,
        HasCustomResponses,
        SendsRequests;

    /**
     * Define the method that the request will use.
     *
     * @var string|null
     */
    protected ?string $method = null;

    /**
     * Define a custom response that the request will return.
     *
     * @var string|null
     */
    protected ?string $response = null;

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

        $isValidRequest = (new ReflectionClass($this->connector))->isSubclassOf(SaloonConnector::class);

        if (! $isValidRequest) {
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
     * Build up the final request URL.
     *
     * @return string
     * @throws SaloonInvalidConnectorException
     */
    public function getFullRequestUrl(): string
    {
        $requestEndpoint = $this->defineEndpoint();

        if ($requestEndpoint !== '/') {
            $requestEndpoint = ltrim($requestEndpoint, '/ ');
        }

        $requiresTrailingSlash = ! empty($requestEndpoint) && $requestEndpoint !== '/';

        $baseEndpoint = rtrim($this->getConnector()->defineBaseUrl(), '/ ');
        $baseEndpoint = $requiresTrailingSlash ? $baseEndpoint . '/' : $baseEndpoint;

        return $baseEndpoint . $requestEndpoint;
    }

    /**
     * Check if a trait exists on the connector.
     *
     * @param string $trait
     * @return bool
     * @throws SaloonInvalidConnectorException
     */
    public function traitExistsOnConnector(string $trait): bool
    {
        return array_key_exists($trait, class_uses($this->getConnector()));
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
