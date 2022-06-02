<?php

namespace Sammyjo20\Saloon\Http;

use ReflectionClass;
use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Enums\Method;
use Sammyjo20\Saloon\Helpers\PluginHelper;
use Sammyjo20\Saloon\Interfaces\AuthenticatorInterface;
use Sammyjo20\Saloon\Traits\HasRequestProperties;

class PendingSaloonRequest
{
    use HasRequestProperties;

    /**
     * The original request class making the request.
     *
     * @var SaloonRequest
     */
    protected SaloonRequest $request;

    /**
     * The original connector making the request.
     *
     * @var SaloonConnector
     */
    protected SaloonConnector $connector;

    /**
     * The method the request will use.
     *
     * @var Method
     */
    protected Method $method;

    /**
     * The mock client if provided on the connector or request.
     *
     * @var MockClient|null
     */
    protected ?MockClient $mockClient = null;

    /**
     * The response class used to create a response.
     *
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
        $this->connector = $connector;
        $this->method = $request->getMethod();
        $this->mockClient = $request->getMockClient() ?? $connector->getMockClient();
        $this->responseClass = $request->getResponseClass() ?? $connector->getResponseClass();

        // 1. Retrieve default properties
        // 2. Merge default properties
        // 3. Merge in new properties, keep old if the content bag allows.
        // 4. Run "boot" methods on connector and request
        // 5. Run authenticator
        // 6. Run all plugins
        // 7. Done!

        $this->mergeRequestProperties()
            ->runAuthenticator()
            ->bootPlugins()
            ->runBeforeSend();
    }

    /**
     * Merge all the properties together.
     *
     * @return $this
     */
    protected function mergeRequestProperties(): self
    {
        $connectorProperties = $this->connector->getRequestProperties();
        $requestProperties = $this->request->getRequestProperties();

        $this->headers()->merge($connectorProperties->headers, $requestProperties->headers);
        $this->queryParameters()->merge($connectorProperties->queryParameters, $requestProperties->queryParameters);
        $this->data()->merge($connectorProperties->data, $requestProperties->data);
        $this->config()->merge($connectorProperties->config, $requestProperties->config);
        $this->guzzleMiddleware()->merge($connectorProperties->guzzleMiddleware, $requestProperties->guzzleMiddleware);
        $this->responseInterceptors()->merge($connectorProperties->responseInterceptors, $requestProperties->responseInterceptors);

        return $this;
    }

    protected function runBootOnConnectorAndRequest(): self
    {
        // Run the "boot" methods on the connector/request.

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
