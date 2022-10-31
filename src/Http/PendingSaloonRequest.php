<?php

namespace Sammyjo20\Saloon\Http;

use Exception;
use ReflectionException;
use Sammyjo20\Saloon\Contracts\MockClient;
use Sammyjo20\Saloon\Enums\Method;
use Sammyjo20\Saloon\Contracts\Sender;
use Sammyjo20\Saloon\Helpers\PluginHelper;
use Sammyjo20\Saloon\Contracts\Authenticator;
use Sammyjo20\Saloon\Contracts\Body\WithBody;
use Sammyjo20\Saloon\Contracts\SaloonResponse;
use Sammyjo20\Saloon\Traits\HasRequestProperties;
use Sammyjo20\Saloon\Traits\AuthenticatesRequests;
use Sammyjo20\Saloon\Contracts\Body\BodyRepository;
use Sammyjo20\Saloon\Http\Middleware\MockMiddleware;
use Sammyjo20\Saloon\Repositories\Body\ArrayBodyRepository;
use Sammyjo20\Saloon\Exceptions\PendingSaloonRequestException;
use Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException;
use Sammyjo20\Saloon\Exceptions\SaloonInvalidResponseClassException;
use Sammyjo20\SaloonLaravel\Middleware\SaloonLaravelMiddleware;

class PendingSaloonRequest
{
    use HasRequestProperties;
    use AuthenticatesRequests;

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
     * The body repository
     *
     * @var BodyRepository|null
     */
    protected ?BodyRepository $body = null;

    /**
     * The simulated response.
     *
     * @var SimulatedResponseData|null
     */
    protected ?SimulatedResponseData $simulatedResponseData = null;

    /**
     * Build up the request payload.
     *
     * @param SaloonRequest $request
     * @param MockClient|null $mockClient
     * @throws PendingSaloonRequestException
     * @throws ReflectionException
     * @throws SaloonInvalidConnectorException
     * @throws SaloonInvalidResponseClassException
     */
    public function __construct(SaloonRequest $request, MockClient $mockClient = null)
    {
        $connector = $request->connector();

        $this->request = $request;
        $this->connector = $connector;
        $this->url = $request->getRequestUrl();
        $this->method = Method::upperFrom($request->getMethod());
        $this->responseClass = $request->getResponseClass();
        $this->mockClient = $mockClient ?? ($request->getMockClient() ?? $connector->getMockClient());
        $this->authenticator = $this->request->getAuthenticator() ?? $this->connector->getAuthenticator();

        // Todo: Document the priority.

        $this->bootPlugins()
            ->mergeRequestProperties()
            ->mergeBody()
            ->authenticateRequest()
            ->bootConnectorAndRequest();

        // Now we will register the default middleware, this always needs to come
        // at the end of the user's defined middleware.

        $this->registerDefaultMiddleware();

        // Finally, we will execute the request middleware pipeline which will
        // process any middleware added on the connector or the request.

        $this->executeRequestPipeline();
    }

    /**
     * Boot every plugin and apply to the payload.
     *
     * @return $this
     * @throws ReflectionException
     */
    protected function bootPlugins(): static
    {
        $connector = $this->connector;
        $request = $this->request;

        $connectorTraits = class_uses_recursive($connector);
        $requestTraits = class_uses_recursive($request);

        foreach ($connectorTraits as $connectorTrait) {
            PluginHelper::bootPlugin($this, $connector, $connectorTrait);
        }

        foreach ($requestTraits as $requestTrait) {
            PluginHelper::bootPlugin($this, $request, $requestTrait);
        }

        return $this;
    }

    /**
     * Merge all the properties together.
     *
     * @return $this
     */
    protected function mergeRequestProperties(): static
    {
        $connector = $this->connector;
        $request = $this->request;

        $this->headers()->merge($connector->headers()->all(), $request->headers()->all());
        $this->queryParameters()->merge($connector->queryParameters()->all(), $request->queryParameters()->all());
        $this->config()->merge($connector->config()->all(), $request->config()->all());

        // Merge together the middleware pipelines...

        $this->middleware()
            ->merge($connector->middleware())
            ->merge($request->middleware());

        return $this;
    }

    /**
     * Merge the body together
     *
     * @return $this
     * @throws PendingSaloonRequestException
     */
    protected function mergeBody(): static
    {
        $connector = $this->connector;
        $request = $this->request;

        $connectorBody = $connector instanceof WithBody ? $connector->body() : null;
        $requestBody = $request instanceof WithBody ? $request->body() : null;

        if (is_null($connectorBody) && is_null($requestBody)) {
            return $this;
        }

        if (isset($connectorBody, $requestBody) && ! $connectorBody instanceof $requestBody) {
            throw new PendingSaloonRequestException('Connector and request body types must be the same.');
        }

        if ($connectorBody instanceof ArrayBodyRepository && $requestBody instanceof ArrayBodyRepository) {
            $repository = clone $connectorBody;
            $repository->merge($requestBody->all());

            $this->body = $repository;

            return $this;
        }

        $this->body = clone $requestBody ?? clone $connectorBody;

        return $this;
    }

    /**
     * Authenticate the request.
     *
     * @return $this
     */
    protected function authenticateRequest(): static
    {
        $authenticator = $this->getAuthenticator();

        if ($authenticator instanceof Authenticator) {
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
     * Register any default middleware that should be placed right at the top.
     *
     * @return $this
     */
    protected function registerDefaultMiddleware(): static
    {
        $middleware = $this->middleware();

        if ($this->isRunningOnLaravel()) {
            $middleware->onRequest(new SaloonLaravelMiddleware);
        }

        $middleware->onRequest(new MockMiddleware);

        return $this;
    }

    /**
     * Execute the request pipeline.
     *
     * @return $this
     */
    protected function executeRequestPipeline(): static
    {
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
        $this->middleware()->executeResponsePipeline($response);

        return $response;
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
     * Get the request sender.
     *
     * @return Sender
     */
    public function getSender(): Sender
    {
        return $this->connector->sender();
    }

    /**
     * Retrieve the body on the pending saloon request
     *
     * @return BodyRepository|null
     */
    public function body(): ?BodyRepository
    {
        return $this->body;
    }

    /**
     * Get the simulated response data
     *
     * @return SimulatedResponseData|null
     */
    public function getSimulatedResponseData(): ?SimulatedResponseData
    {
        return $this->simulatedResponseData;
    }

    /**
     * Set the simulated response data
     *
     * @param SimulatedResponseData|null $simulatedResponseData
     * @return PendingSaloonRequest
     */
    public function setSimulatedResponseData(?SimulatedResponseData $simulatedResponseData): PendingSaloonRequest
    {
        $this->simulatedResponseData = $simulatedResponseData;

        return $this;
    }

    /**
     * Check if simulated response data is present.
     *
     * @return bool
     */
    public function hasSimulatedResponseData(): bool
    {
        return $this->simulatedResponseData instanceof SimulatedResponseData;
    }

    /**
     * Set a mock client on the pending request.
     *
     * @param MockClient|null $mockClient
     * @return PendingSaloonRequest
     */
    public function setMockClient(?MockClient $mockClient): PendingSaloonRequest
    {
        $this->mockClient = $mockClient;

        return $this;
    }

    /**
     * Check if Saloon is running on Laravel
     *
     * @return bool
     */
    protected function isRunningOnLaravel(): bool
    {
        try {
            return function_exists('resolve') && resolve('saloon') instanceof \Sammyjo20\SaloonLaravel\Saloon && class_exists(SaloonLaravelMiddleware::class);
        } catch (Exception $ex) {
            return false;
        }
    }
}
