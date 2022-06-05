<?php

namespace Sammyjo20\Saloon\Http;

use ReflectionClass;
use GuzzleHttp\Psr7\Request;
use Sammyjo20\Saloon\Enums\Method;
use Sammyjo20\Saloon\Data\DataType;
use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Helpers\MiddlewarePipeline;
use Sammyjo20\Saloon\Helpers\PluginHelper;
use Sammyjo20\Saloon\Http\Middleware\DataObjectPipe;
use Sammyjo20\Saloon\Interfaces\Data\HasJsonBody;
use Sammyjo20\Saloon\Traits\HasRequestProperties;
use Sammyjo20\Saloon\Interfaces\Data\HasMixedBody;
use Sammyjo20\Saloon\Interfaces\Data\HasFormParams;
use Sammyjo20\Saloon\Interfaces\Data\HasMultipartBody;
use Sammyjo20\Saloon\Interfaces\AuthenticatorInterface;
use Sammyjo20\Saloon\Exceptions\PendingSaloonRequestException;

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
     * @var MiddlewarePipeline
     */
    protected MiddlewarePipeline $connectorMiddleware;

    /**
     * @var MiddlewarePipeline
     */
    protected MiddlewarePipeline $requestMiddleware;

    /**
     * @var DataType|null
     */
    protected ?DataType $dataType = null;

    /**
     * Build up the request payload.
     *
     * @param SaloonRequest $request
     * @throws PendingSaloonRequestException
     * @throws \ReflectionException
     * @throws \Sammyjo20\Saloon\Exceptions\DataBagException
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

        $this->mergeRequestProperties()
            ->mergeData()
            ->runAuthenticator()
            ->bootConnectorAndRequest()
            ->bootPlugins()
            ->registerDefaultMiddleware()
            ->executeRequestPipeline();
    }

    /**
     * Merge all the properties together.
     *
     * @return $this
     * @throws \Sammyjo20\Saloon\Exceptions\DataBagException
     */
    protected function mergeRequestProperties(): self
    {
        $connectorProperties = $this->connector->getRequestProperties();
        $requestProperties = $this->request->getRequestProperties();

        $this->headers()->merge($connectorProperties->headers, $requestProperties->headers);
        $this->queryParameters()->merge($connectorProperties->queryParameters, $requestProperties->queryParameters);
        $this->config()->merge($connectorProperties->config, $requestProperties->config);

        return $this;
    }

    /**
     * @return $this
     * @throws PendingSaloonRequestException
     * @throws \Sammyjo20\Saloon\Exceptions\DataBagException
     */
    protected function mergeData(): self
    {
        $connectorProperties = $this->connector->getRequestProperties();
        $requestProperties = $this->request->getRequestProperties();

        $connectorDataType = $this->determineDataType($this->connector);
        $requestDataType = $this->determineDataType($this->request);

        if (isset($connectorDataType, $requestDataType) && $connectorDataType !== $requestDataType) {
            throw new PendingSaloonRequestException('Request data type and connector data type cannot be mixed.');
        }

        // The primary data type will be the request data type, if one has not
        // been set, we will use the connector data.

        $dataType = $requestDataType ?? $connectorDataType;

        if ($connectorDataType instanceof DataType) {
            $connectorDataType->isArrayable()
                ? $this->data()->merge($connectorProperties->data)
                : $this->data()->set($connectorProperties->data);
        }

        if ($requestDataType instanceof DataType) {
            $requestDataType->isArrayable()
                ? $this->data()->merge($requestProperties->data)
                : $this->data()->set($requestProperties->data);
        }

        $this->dataType = $dataType;

        return $this;
    }

    /**
     * @return DataType|null
     */
    public function getDataType(): ?DataType
    {
        return $this->dataType;
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
    protected function bootConnectorAndRequest(): self
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

    protected function registerDefaultMiddleware(): self
    {
        // Todo: Register high priority mock client
        // Todo: Register DTO response pipe

        $this->middleware()
            ->addResponsePipe(new DataObjectPipe);

        return $this;
    }

    /**
     * Execute the request pipeline.
     *
     * @return $this
     */
    protected function executeRequestPipeline(): self
    {
        // Todo: Combine into one Middleware pipeline.

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
    public function executeResponsePipeline(SaloonResponse $response): SaloonResponse
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
     * Calculate the data type.
     *
     * @param SaloonConnector|SaloonRequest $object
     * @return DataType|null
     */
    protected function determineDataType(SaloonConnector|SaloonRequest $object): ?DataType
    {
        if ($object instanceof HasJsonBody) {
            return DataType::JSON;
        }

        if ($object instanceof HasFormParams) {
            return DataType::FORM;
        }

        if ($object instanceof HasMultipartBody) {
            return DataType::MULTIPART;
        }

        if ($object instanceof HasMixedBody) {
            return DataType::MIXED;
        }

        return null;
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
