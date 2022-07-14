<?php

namespace Sammyjo20\Saloon\Http;

use ReflectionClass;
use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Data\DataBagType;
use Sammyjo20\Saloon\Data\RequestDataType;
use Sammyjo20\Saloon\Enums\Method;
use Sammyjo20\Saloon\Exceptions\PendingSaloonRequestException;
use Sammyjo20\Saloon\Helpers\PluginHelper;
use Sammyjo20\Saloon\Http\Middleware\MockResponsePipe;
use Sammyjo20\Saloon\Http\Responses\SaloonResponse;
use Sammyjo20\Saloon\Interfaces\AuthenticatorInterface;
use Sammyjo20\Saloon\Interfaces\Data\SendsFormParams;
use Sammyjo20\Saloon\Interfaces\Data\SendsJsonBody;
use Sammyjo20\Saloon\Interfaces\Data\SendsMixedBody;
use Sammyjo20\Saloon\Interfaces\Data\SendsMultipartBody;
use Sammyjo20\Saloon\Interfaces\Data\SendsXMLBody;
use Sammyjo20\Saloon\Interfaces\SenderInterface;
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
     * The early response.
     *
     * @var SaloonResponse|null
     */
    protected ?SaloonResponse $earlyResponse = null;

    /**
     * The data type.
     *
     * @var RequestDataType|null
     */
    protected ?RequestDataType $dataType = null;

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
        $this->method = Method::upperFrom($request->getMethod());
        $this->responseClass = $request->getResponseClass();
        $this->mockClient = $request->getMockClient() ?? $connector->getMockClient();

        // Now it's time to stitch together our PendingSaloonRequest. We will firstly merge everything
        // into this one class, and then run each of the various features at once. 🚀

        $this
            ->mergeRequestProperties()
            ->mergeData()
            ->authenticateRequest()
            ->bootConnectorAndRequest()
            ->bootPlugins()
            ->registerDefaultMiddleware()
            ->executeRequestPipeline();
    }

    /**
     * Merge all the properties together.
     *
     * @return $this
     */
    protected function mergeRequestProperties(): static
    {
        $connectorProperties = $this->connector->getRequestProperties();
        $requestProperties = $this->request->getRequestProperties();

        $this->headers()->merge($connectorProperties->headers, $requestProperties->headers);
        $this->queryParameters()->merge($connectorProperties->queryParameters, $requestProperties->queryParameters);
        $this->config()->merge($connectorProperties->config, $requestProperties->config);

        // Merge together the middleware pipelines...

        $this->middlewarePipeline()
            ->merge($connectorProperties->middleware)
            ->merge($requestProperties->middleware);

        return $this;
    }

    /**
     * Merge together the data.
     *
     * @return $this
     * @throws PendingSaloonRequestException
     * @throws \Sammyjo20\Saloon\Exceptions\DataBagException
     */
    protected function mergeData(): static
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

        // If no data type was found, just continue.

        if (! $dataType instanceof RequestDataType) {
            if ($this->data()->isEmpty()) {
                return $this;
            }

            throw new PendingSaloonRequestException('You have provided data without a data type interface defined on your request or connector.');
        }

        // Now we'll enforce the type on the data.

        $this->data()->setTypeFromRequestType($dataType);
        $this->dataType = $dataType;

        // Now we'll set the data. If the data type is arrayable, we'll merge it together.
        // If it's a mixed data type, we'll just set the data.

        $connectorData = $connectorProperties->data;
        $requestData = $requestProperties->data;

        if ($dataType->isArrayable()) {
            $this->data()->merge($connectorData ?? [], $requestData ?? []);
        }

        if ($dataType === RequestDataType::MIXED) {
            if (! empty($connectorData)) {
                $this->data()->set($connectorData);
            }

            if (! empty($requestData)) {
                $this->data()->set($requestData);
            }
        }

        return $this;
    }

    /**
     * Authenticate the request.
     *
     * @return $this
     */
    protected function authenticateRequest(): static
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
    protected function bootConnectorAndRequest(): static
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
    protected function bootPlugins(): static
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
     * Register any default middleware that should be placed right at the top.
     *
     * @return $this
     */
    protected function registerDefaultMiddleware(): static
    {
        $pipeline = $this->middlewarePipeline();

        // Todo: Register Laravel middleware pipe.

        return $this;
    }

    /**
     * Execute the request pipeline.
     *
     * @return $this
     */
    protected function executeRequestPipeline(): static
    {
        $this->middlewarePipeline()->executeRequestPipeline($this);

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
        $this->middlewarePipeline()->executeResponsePipeline($response);

        return $response;
    }

    /**
     * Calculate the data type.
     *
     * @param SaloonConnector|SaloonRequest $object
     * @return RequestDataType|null
     */
    protected function determineDataType(SaloonConnector|SaloonRequest $object): ?RequestDataType
    {
        if ($object instanceof SendsJsonBody) {
            return RequestDataType::JSON;
        }

        if ($object instanceof SendsFormParams) {
            return RequestDataType::FORM;
        }

        if ($object instanceof SendsMultipartBody) {
            return RequestDataType::MULTIPART;
        }

        if ($object instanceof SendsMixedBody || $object instanceof SendsXMLBody) {
            return RequestDataType::MIXED;
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

    /**
     * Check if the pending Saloon request is being mocked.
     *
     * @return bool
     */
    public function isMocking(): bool
    {
        return $this->mockClient instanceof MockClient;
    }

    /**
     * @return RequestDataType|null
     */
    public function getDataType(): ?RequestDataType
    {
        return $this->dataType;
    }

    /**
     * Get the request sender.
     *
     * @return SenderInterface
     */
    public function getSender(): SenderInterface
    {
        return $this->connector->sender();
    }

    /**
     * @return SaloonResponse|null
     */
    public function getEarlyResponse(): ?SaloonResponse
    {
        return $this->earlyResponse;
    }

    /**
     * @return bool
     */
    public function hasEarlyResponse(): bool
    {
        return $this->earlyResponse instanceof SaloonResponse;
    }

    /**
     * @param SaloonResponse|null $earlyResponse
     * @return PendingSaloonRequest
     */
    public function setEarlyResponse(?SaloonResponse $earlyResponse): static
    {
        $this->earlyResponse = $earlyResponse;

        return $this;
    }

    /**
     * Set the mock client
     *
     * @param MockClient|null $mockClient
     * @return PendingSaloonRequest
     */
    public function setMockClient(?MockClient $mockClient): static
    {
        $this->mockClient = $mockClient;

        return $this;
    }
}
