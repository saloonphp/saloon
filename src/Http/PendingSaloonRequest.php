<?php declare(strict_types=1);

namespace Sammyjo20\Saloon\Http;

use ReflectionException;
use Sammyjo20\Saloon\Enums\Method;
use Sammyjo20\Saloon\Contracts\Sender;
use Sammyjo20\Saloon\Helpers\Environment;
use Sammyjo20\Saloon\Contracts\MockClient;
use Sammyjo20\Saloon\Helpers\PluginHelper;
use Sammyjo20\Saloon\Contracts\Body\WithBody;
use Sammyjo20\Saloon\Contracts\SaloonResponse;
use Sammyjo20\Saloon\Contracts\Body\BodyRepository;
use Sammyjo20\Saloon\Traits\Auth\AuthenticatesRequests;
use Sammyjo20\Saloon\Http\Middleware\AuthenticateRequest;
use Sammyjo20\Saloon\Http\Faking\SimulatedResponsePayload;
use Sammyjo20\Saloon\Http\Middleware\DetermineMockResponse;
use Sammyjo20\Saloon\Repositories\Body\ArrayBodyRepository;
use Sammyjo20\Saloon\Exceptions\PendingSaloonRequestException;
use Sammyjo20\SaloonLaravel\Middleware\SaloonLaravelMiddleware;
use Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException;
use Sammyjo20\Saloon\Traits\RequestProperties\HasRequestProperties;
use Sammyjo20\Saloon\Exceptions\SaloonInvalidResponseClassException;

class PendingSaloonRequest
{
    use HasRequestProperties;
    use AuthenticatesRequests;

    /**
     * The request used by the instance.
     *
     * @var SaloonRequest
     */
    protected SaloonRequest $request;

    /**
     * The connector making the request.
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
     * The class used for responses.
     *
     * @var string
     */
    protected string $responseClass;

    /**
     * The mock client used to replace requests.
     *
     * @var MockClient|null
     */
    protected ?MockClient $mockClient = null;

    /**
     * The body of the request.
     *
     * @var BodyRepository|null
     */
    protected ?BodyRepository $body = null;

    /**
     * The simulated response.
     *
     * @var SimulatedResponsePayload|null
     */
    protected ?SimulatedResponsePayload $simulatedResponsePayload = null;

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

        // After we have defined each of our properties, we will run the various
        // methods that build up the PendingSaloonRequest. It's important that
        // the order remains the same.

        // Plugins should be booted first, then we will merge the properties
        // from the connector and request, then authenticate the request
        // followed by finally running the "boot" method with an
        // almost complete PendingSaloonRequest.

        $this->bootPlugins()
            ->mergeRequestProperties()
            ->mergeBody()
            ->bootConnectorAndRequest();

        // Now we will register the default middleware, this always needs to come
        // at the end of the user's defined middleware.

        $this->registerDefaultMiddleware();

        // Finally, we will execute the request middleware pipeline which will
        // process any middleware added on the connector or the request.

        $this->executeRequestPipeline();
    }

    /**
     * Boot every plugin on the connector and request.
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
     * Register any default middleware to run at the end of the middleware stack.
     *
     * @return $this
     */
    protected function registerDefaultMiddleware(): static
    {
        $middleware = $this->middleware();

        $middleware->onRequest(new AuthenticateRequest);

        if (Environment::detectsLaravel() && class_exists(SaloonLaravelMiddleware::class)) {
            $middleware->onRequest(new SaloonLaravelMiddleware);
        }

        $middleware->onRequest(new DetermineMockResponse);

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
     * Execute the response pipeline.
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
     * Get the request.
     *
     * @return SaloonRequest
     */
    public function getRequest(): SaloonRequest
    {
        return $this->request;
    }

    /**
     * Get the conector.
     *
     * @return SaloonConnector
     */
    public function getConnector(): SaloonConnector
    {
        return $this->connector;
    }

    /**
     * Get the URL of the request.
     *
     * @return string
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Get the HTTP method used for the request
     *
     * @return Method
     */
    public function getMethod(): Method
    {
        return $this->method;
    }

    /**
     * Get the response class used for the request
     *
     * @return string
     */
    public function getResponseClass(): string
    {
        return $this->responseClass;
    }

    /**
     * Get the mock client.
     *
     * @return MockClient|null
     */
    public function getMockClient(): ?MockClient
    {
        return $this->mockClient;
    }

    /**
     * Determine if the instance is "mocking"
     *
     * @return bool
     */
    public function hasMockClient(): bool
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
     * Retrieve the body on the instance
     *
     * @return BodyRepository|null
     */
    public function body(): ?BodyRepository
    {
        return $this->body;
    }

    /**
     * Get the simulated response payload
     *
     * @return SimulatedResponsePayload|null
     */
    public function getSimulatedResponsePayload(): ?SimulatedResponsePayload
    {
        return $this->simulatedResponsePayload;
    }

    /**
     * Set the simulated response payload
     *
     * @param SimulatedResponsePayload|null $simulatedResponsePayload
     * @return PendingSaloonRequest
     */
    public function setSimulatedResponsePayload(?SimulatedResponsePayload $simulatedResponsePayload): PendingSaloonRequest
    {
        $this->simulatedResponsePayload = $simulatedResponsePayload;

        return $this;
    }

    /**
     * Check if simulated response payload is present.
     *
     * @return bool
     */
    public function hasSimulatedResponsePayload(): bool
    {
        return $this->simulatedResponsePayload instanceof SimulatedResponsePayload;
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
}
