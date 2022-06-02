<?php

namespace Sammyjo20\Saloon\Http;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Utils;
use ReflectionClass;
use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Enums\Method;
use Sammyjo20\Saloon\Helpers\Middleware;
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
     * The URL the request will be made to.
     *
     * @var string
     */
    protected string $url;

    /**
     * The method the request will use.
     *
     * @var Method
     */
    protected Method $method;

    /**
     * The response class used to create a response.
     *
     * @var string
     */
    protected string $responseClass;

    /**
     * The mock client if provided on the connector or request.
     *
     * @var MockClient|null
     */
    protected ?MockClient $mockClient = null;

    /**
     * @var Middleware
     */
    protected Middleware $connectorMiddleware;

    /**
     * @var Middleware
     */
    protected Middleware $requestMiddleware;

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
        $this->url = $request->getRequestUrl();
        $this->method = $request->getMethod();
        $this->responseClass = $request->getResponseClass();
        $this->mockClient = $request->getMockClient() ?? $connector->getMockClient();
        $this->connectorMiddleware = $connector->middleware();
        $this->requestMiddleware = $request->middleware();

        // Todo: Maybe Validate Response Class Here?

        $this->mergeRequestProperties()
            ->runAuthenticator()
            ->runBootOnConnectorAndRequest()
            ->bootPlugins()
            ->runMiddlewarePipeline();
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

        return $this;
    }

    /**
     * Authenticate the request.
     *
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
     * Run the boot method on the connector and request.
     *
     * @return $this
     */
    protected function runBootOnConnectorAndRequest(): self
    {
        $this->connector->boot($this);
        $this->request->boot($this);

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
    protected function runMiddlewarePipeline(): self
    {
        $this->connectorMiddleware->executeRequestPipeline($this);
        $this->requestMiddleware->executeRequestPipeline($this);
        $this->middleware()->executeRequestPipeline($this);

        return $this;
    }

    /**
     * Run the response through a pipeline
     *
     * @param SaloonResponse $response
     * @return SaloonResponse
     */
    public function runResponsePipeline(SaloonResponse $response): SaloonResponse
    {
        $response = $this->connectorMiddleware->executeResponsePipeline($response);
        $response = $this->requestMiddleware->executeResponsePipeline($response);
        $response = $this->middleware()->executeResponsePipeline($response);

        return $response;
    }

    public function toPsrRequest(): Request
    {
        // TODO: Work out data properly

        return new Request($this->method->value, $this->url, $this->headers()->all(), $this->data()->all());
    }

    /**
     * @return SaloonRequest
     */
    public function getRequest(): SaloonRequest
    {
        return $this->request;
    }

    /**
     * @return SaloonConnector
     */
    public function getConnector(): SaloonConnector
    {
        return $this->connector;
    }

    /**
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * @return Method
     */
    public function getMethod(): Method
    {
        return $this->method;
    }

    /**
     * @return string
     */
    public function getResponseClass(): string
    {
        return $this->responseClass;
    }


    /**
     * @return MockClient|null
     */
    public function getMockClient(): ?MockClient
    {
        return $this->mockClient;
    }
}
