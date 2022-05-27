<?php

namespace Sammyjo20\Saloon\Http;

use ReflectionClass;
use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Enums\Method;
use Sammyjo20\Saloon\Helpers\PluginHelper;
use Sammyjo20\Saloon\Interfaces\AuthenticatorInterface;
use Sammyjo20\Saloon\Traits\HasRequestProperties;

// This contains all the merged in data from the connector and the request.
// It acts like a data-transfer-object with extra things included.

// 1. Construct and accept a request
// 2. Boot or get the connector
// 3. Pull in the request data from the request + connector
// 4. Merge into the request payload's data.

// 1. "boot" the connector and the request, pass in the request payload
// 2. "boot" all the plugins, pass in the request payload
// 3. Run the authenticator
// 4. Run the laravel manager and merge in any data from there...

// Now You have a complete request payload with everything inside ready to be sent to the manager!

// Request payload data:
// - Request, Connector
// - Headers, Config, Data, GuzzleMiddleware, Response Interceptors

// ...

// Inside a "RequestSender", accept a RequestPayload and we can do whatever we want with it

// Mock client could live on the connector or the request.

class RequestPayload
{
    use HasRequestProperties;

    /**
     * @var SaloonRequest
     */
    protected SaloonRequest $request;

    /**
     * @var Method
     */
    protected Method $method;

    /**
     * @var SaloonConnector
     */
    protected SaloonConnector $connector;

    /**
     * @var MockClient|null
     */
    protected ?MockClient $mockClient = null;

    /**
     * @var string
     */
    protected string $responseClass;

    /**
     * Build up the request payload.
     *
     * @param SaloonRequest $request
     * @throws \ReflectionException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidResponseClassException
     */
    public function __construct(SaloonRequest $request)
    {
        $connector = $request->getConnector();

        $this->request = $request;
        $this->method = $request->getMethod();
        $this->connector = $connector;
        $this->mockClient = $request->getMockClient() ?? $connector->getMockClient();
        $this->responseClass = $request->getResponseClass() ?? $connector->getResponseClass();

        $this->mergeBaseProperties()
            ->bootPlugins()
            ->runAuthenticator()
            ->runBeforeSend();
    }

    /**
     * Merge all the base data properties from the connectors.
     *
     * @return $this
     */
    protected function mergeBaseProperties(): self
    {
        $connector = $this->connector;
        $request = $this->request;

        $this->headers->merge(
            $connector->headers->all(),
            $request->headers->all());

        $this->queryParameters->merge(
            $connector->queryParameters->all(),
            $request->queryParameters->all(),
        );

        $this->data->merge(
            $connector->data->all(),
            $request->data->all(),
        );

        $this->config->merge(
            $connector->config->all(),
            $request->config->all(),
        );

        $this->guzzleMiddleware->merge(
            $connector->guzzleMiddleware->all(),
            $request->guzzleMiddleware->all(),
        );

        $this->responseInterceptors->merge(
            $connector->responseInterceptors->all(),
            $request->responseInterceptors->all(),
        );

        return $this;
    }

    /**
     * Boot every plugin and apply to the payload.
     *
     * @return $this
     * @throws \ReflectionException
     */
    protected function bootPlugins(): self
    {
        $connector = $this->connector;
        $request = $this->request;

        $connectorTraits = (new ReflectionClass($connector))->getTraits();
        $requestTraits = (new ReflectionClass($request))->getTraits();

        foreach ($connectorTraits as $connectorTrait) {
            PluginHelper::bootPlugin($this, $connector, $connectorTrait);
        }

        foreach ($requestTraits as $requestTrait) {
            PluginHelper::bootPlugin($this, $request, $requestTrait);
        }

        return $this;
    }

    /**
     * @return $this
     */
    protected function runAuthenticator(): self
    {
        $authenticator = $this->request->getAuthenticator() ?? $this->connector->getAuthenticator();

        if ($authenticator instanceof AuthenticatorInterface) {
            $authenticator->set($this);
        }

        return $this;
    }

    /**
     * Run the "boot" methods on the connector and the request.
     *
     * @return $this
     */
    protected function runBeforeSend(): self
    {
        $this->connector->beforeSend($this);
        $this->request->beforeSend($this);

        return $this;
    }
}
