<?php

namespace Sammyjo20\Saloon\Http;

use Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException;
use Sammyjo20\Saloon\Exceptions\SaloonMethodNotFoundException;
use Sammyjo20\Saloon\Interfaces\SaloonRequestInterface;
use Sammyjo20\Saloon\Traits\CollectsAuth;
use Sammyjo20\Saloon\Traits\CollectsConfig;
use Sammyjo20\Saloon\Traits\CollectsData;
use Sammyjo20\Saloon\Traits\CollectsHeaders;
use Sammyjo20\Saloon\Traits\CollectsAttributes;
use Sammyjo20\Saloon\Traits\InterceptsGuzzle;
use Sammyjo20\Saloon\Traits\SendsRequests;

abstract class SaloonRequest implements SaloonRequestInterface
{
    use CollectsData,
        CollectsHeaders,
        CollectsAuth,
        CollectsConfig,
        CollectsAttributes;

    use SendsRequests;
    use InterceptsGuzzle;

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
     * @var SaloonConnector
     */
    private SaloonConnector $loadedConnector;

    public function __construct(array $requestAttributes = [])
    {
        // Bootstrap our request
        // Todo: Tidy  up the below - note "options" and "data" is basically the same thing.
        // Todo: Throw exceptions if certain attributes aren't listed.
        // Todo: Throw exceptions if requested headers, attributes or data is missing.

        // Todo: Work out how to access "auth", "data", and "config" from within each other.
        // Todo: Setting the defaults may not work here.

        $this->bootConnector($this->connector);

        $this->setRequestAttributes($requestAttributes);
        $this->setAuth($this->defineAuth());
        $this->setData($this->defineData());
    }

    public function defineMethod(): ?string
    {
        if (empty($this->method)) {
            return null;
        }

        return $this->method;
    }

    public function bootConnector(string $connectorClass = null): void
    {
        if (empty($this->connector) || ! class_exists($this->connector)) {
            throw new SaloonInvalidConnectorException;
        }

        $this->loadedConnector = new $connectorClass;
    }

    public function getConnector(): SaloonConnector
    {
        return $this->loadedConnector;
    }

    public function mockSuccessResponse(): array
    {
        // Todo: Change this as it won't work if you want to define the success/failure response.

        return [
            'status' => 201,
            'headers' => [],
            'body' => 'Success',
        ];
    }

    public function mockFailureResponse(): array
    {
        return [
            'status' => 401,
            'headers' => [],
            'body' => '',
        ];
    }

    /**
     * Default "body" if you do not provide your own.
     *
     * @return array
     */
    public function defineBody(): array
    {
        return $this->getData();
    }

    /**
     * Dynamically proxy other methods to the underlying response.
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
