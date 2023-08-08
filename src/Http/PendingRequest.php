<?php

declare(strict_types=1);

namespace Saloon\Http;

use Saloon\Enums\Method;
use Saloon\Helpers\Config;
use Saloon\Helpers\Helpers;
use Saloon\Contracts\Request;
use Saloon\Helpers\URLHelper;
use Saloon\Contracts\Connector;
use Saloon\Contracts\MockClient;
use Saloon\Helpers\PluginHelper;
use Saloon\Traits\Conditionable;
use Saloon\Traits\HasMockClient;
use Psr\Http\Message\UriInterface;
use Saloon\Contracts\FakeResponse;
use Saloon\Data\FactoryCollection;
use Saloon\Contracts\Authenticator;
use Saloon\Helpers\ReflectionHelper;
use Saloon\Http\Middleware\MergeBody;
use Psr\Http\Message\RequestInterface;
use Saloon\Http\Middleware\MergeDelay;
use Saloon\Http\Middleware\DebugRequest;
use Saloon\Contracts\Body\BodyRepository;
use Saloon\Http\Middleware\DebugResponse;
use Saloon\Http\Middleware\DelayMiddleware;
use Saloon\Traits\Auth\AuthenticatesRequests;
use Saloon\Http\Middleware\AuthenticateRequest;
use Saloon\Http\Middleware\DetermineMockResponse;
use Saloon\Contracts\Response as ResponseContract;
use Saloon\Http\Middleware\MergeRequestProperties;
use Saloon\Exceptions\InvalidResponseClassException;
use Saloon\Traits\PendingRequest\CreatesFakeResponses;
use Saloon\Traits\RequestProperties\HasRequestProperties;
use Saloon\Contracts\PendingRequest as PendingRequestContract;

class PendingRequest implements PendingRequestContract
{
    use AuthenticatesRequests;
    use HasRequestProperties;
    use CreatesFakeResponses;
    use Conditionable;
    use HasMockClient;

    /**
     * The connector making the request.
     *
     * @var \Saloon\Contracts\Connector
     */
    protected Connector $connector;

    /**
     * The request used by the instance.
     *
     * @var \Saloon\Contracts\Request
     */
    protected Request $request;

    /**
     * The method the request will use.
     *
     * @var \Saloon\Enums\Method
     */
    protected Method $method;

    /**
     * The URL the request will be made to.
     *
     * @var string
     */
    protected string $url;

    /**
     * The class used for responses.
     *
     * @var class-string<\Saloon\Contracts\Response>
     */
    protected string $responseClass;

    /**
     * The body of the request.
     *
     * @var \Saloon\Contracts\Body\BodyRepository|null
     */
    protected ?BodyRepository $body = null;

    /**
     * The simulated response.
     *
     * @var \Saloon\Contracts\FakeResponse|null
     */
    protected ?FakeResponse $fakeResponseData = null;

    /**
     * Determine if the pending request is asynchronous
     *
     * @var bool
     */
    protected bool $asynchronous = false;

    /**
     * The factory collection.
     *
     * @var FactoryCollection
     */
    protected FactoryCollection $factoryCollection;

    /**
     * Build up the request payload.
     *
     * @param \Saloon\Contracts\Connector $connector
     * @param \Saloon\Contracts\Request $request
     * @param \Saloon\Contracts\MockClient|null $mockClient
     * @throws \ReflectionException
     * @throws \Saloon\Exceptions\InvalidResponseClassException
     */
    public function __construct(Connector $connector, Request $request, MockClient $mockClient = null)
    {
        $this->connector = $connector;
        $this->request = $request;
        $this->method = $request->getMethod();
        $this->url = $this->resolveRequestUrl();
        $this->authenticator = $request->getAuthenticator() ?? $connector->getAuthenticator();
        $this->responseClass = $this->resolveResponseClass();
        $this->mockClient = $mockClient ?? $request->getMockClient() ?? $connector->getMockClient();
        $this->factoryCollection = $connector->sender()->getFactoryCollection();

        $this->registerAndExecuteMiddleware();
    }

    /**
     * Register and execute middleware
     *
     * @return void
     * @throws \ReflectionException
     */
    protected function registerAndExecuteMiddleware(): void
    {
        $middleware = $this->middleware();

        // New Middleware Order:

        // 1. Global (Laravel)
        // 2. Plugin (Rate Limiter)
        // 3. Authentication
        // 4. Mock Response
        // 5. User
        // 6. Delay/Debugging/Event

        $middleware->merge(Config::middleware());

        $this->bootPlugins();

        // Now we'll queue te delay middleware and authenticator middleware

        $middleware
            ->onRequest(new MergeRequestProperties, false, 'mergeRequestProperties')
            ->onRequest(new MergeBody, false, 'mergeBody')
            ->onRequest(new MergeDelay, false, 'mergeDelay')
            ->onRequest(new AuthenticateRequest, false, 'authenticateRequest')
            ->onRequest(new DetermineMockResponse, false, 'determineMockResponse');

        $this->bootConnectorAndRequest();

        $middleware
            ->merge($this->connector->middleware())
            ->merge($this->request->middleware())
            ->onRequest(new DelayMiddleware, false, 'delayMiddleware')
            ->onRequest(new DebugRequest, false, 'debugRequest')
            ->onResponse(new DebugResponse, false, 'debugResponse');

        // Next, we will execute the request middleware pipeline which will
        // process any middleware added on the connector or the request.

        $middleware->executeRequestPipeline($this);
    }

    /**
     * Boot every plugin on the connector and request.
     *
     * @return $this
     * @throws \ReflectionException
     */
    protected function bootPlugins(): static
    {
        $connector = $this->connector;
        $request = $this->request;

        $connectorTraits = Helpers::classUsesRecursive($connector);
        $requestTraits = Helpers::classUsesRecursive($request);

        foreach ($connectorTraits as $connectorTrait) {
            Helpers::bootPlugin($this, $connector, $connectorTrait);
        }

        foreach ($requestTraits as $requestTrait) {
            Helpers::bootPlugin($this, $request, $requestTrait);
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
        // This method is not going to be part of a middleware because the
        // users may wish to register middleware inside the boot methods.

        $this->connector->boot($this);
        $this->request->boot($this);

        return $this;
    }

    /**
     * Get the request.
     *
     * @return \Saloon\Contracts\Request
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Execute the response pipeline.
     *
     * @param \Saloon\Contracts\Response $response
     * @return \Saloon\Contracts\Response
     */
    public function executeResponsePipeline(ResponseContract $response): ResponseContract
    {
        return $this->middleware()->executeResponsePipeline($response);
    }


    /**
     * Get the connector.
     *
     * @return \Saloon\Contracts\Connector
     */
    public function getConnector(): Connector
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
     * Get the URI for the pending request.
     *
     * @return UriInterface
     */
    public function getUri(): UriInterface
    {
        $uri = $this->factoryCollection->uriFactory->createUri($this->getUrl());

        // We'll parse the existing query parameters from the URL (if they have been defined)
        // and then we'll merge in Saloon's query parameters. Our query parameters will take
        // priority over any that were defined in the URL.

        parse_str($uri->getQuery(), $existingQuery);

        return $uri->withQuery(
            http_build_query(array_merge($existingQuery, $this->query()->all()))
        );
    }

    /**
     * Get the HTTP method used for the request
     *
     * @return \Saloon\Enums\Method
     */
    public function getMethod(): Method
    {
        return $this->method;
    }

    /**
     * Get the response class used for the request
     *
     * @return class-string<\Saloon\Contracts\Response>
     */
    public function getResponseClass(): string
    {
        return $this->responseClass;
    }

    /**
     * Retrieve the body on the instance
     *
     * @return \Saloon\Contracts\Body\BodyRepository|null
     */
    public function body(): ?BodyRepository
    {
        return $this->body;
    }

    /**
     * Get the simulated response payload
     *
     * @return \Saloon\Contracts\FakeResponse|null
     */
    public function getFakeResponse(): ?FakeResponse
    {
        return $this->fakeResponseData;
    }

    /**
     * Set the simulated response payload
     *
     * @param \Saloon\Contracts\FakeResponse|null $fakeResponse
     * @return $this
     */
    public function setFakeResponse(?FakeResponse $fakeResponse): static
    {
        $this->fakeResponseData = $fakeResponse;

        return $this;
    }

    /**
     * Check if simulated response payload is present.
     *
     * @return bool
     */
    public function hasFakeResponse(): bool
    {
        return $this->fakeResponseData instanceof FakeResponse;
    }

    /**
     * Build up the full request URL.
     *
     * @return string
     */
    protected function resolveRequestUrl(): string
    {
        return URLHelper::join($this->connector->resolveBaseUrl(), $this->request->resolveEndpoint());
    }

    /**
     * Get the response class
     *
     * @return class-string<\Saloon\Contracts\Response>
     * @throws \ReflectionException
     * @throws \Saloon\Exceptions\InvalidResponseClassException
     */
    protected function resolveResponseClass(): string
    {
        $response = $this->request->resolveResponseClass() ?? $this->connector->resolveResponseClass() ?? Response::class;

        if (! class_exists($response) || ! Helpers::isSubclassOf($response, ResponseContract::class)) {
            throw new InvalidResponseClassException;
        }

        return $response;
    }

    /**
     * Create a data object from the response
     *
     * @param \Saloon\Contracts\Response $response
     * @return mixed
     */
    public function createDtoFromResponse(ResponseContract $response): mixed
    {
        return $this->request->createDtoFromResponse($response) ?? $this->connector->createDtoFromResponse($response);
    }

    /**
     * Set if the request is going to be sent asynchronously
     *
     * @param bool $asynchronous
     * @return $this
     */
    public function setAsynchronous(bool $asynchronous): static
    {
        $this->asynchronous = $asynchronous;

        return $this;
    }

    /**
     * Check if the request is asynchronous
     *
     * @return bool
     */
    public function isAsynchronous(): bool
    {
        return $this->asynchronous;
    }

    /**
     * Authenticate the PendingRequest
     *
     * @param \Saloon\Contracts\Authenticator $authenticator
     * @return $this
     */
    public function authenticate(Authenticator $authenticator): static
    {
        $this->authenticator = $authenticator;

        // If the PendingRequest has already been constructed, it would be nice
        // for someone to be able to run the "authenticate" method after. This
        // will allow us to do this. With future versions of Saloon we will
        // likely remove this method.

        $this->authenticator->set($this);

        return $this;
    }

    /**
     * Set the URL of the PendingRequest
     *
     * @param string $url
     * @return $this
     */
    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Set the method of the PendingRequest
     *
     * @param \Saloon\Enums\Method $method
     * @return $this
     */
    public function setMethod(Method $method): static
    {
        $this->method = $method;

        return $this;
    }

    /**
     * Set the factory collection
     *
     * @param FactoryCollection $factoryCollection
     * @return $this
     */
    public function setFactoryCollection(FactoryCollection $factoryCollection): static
    {
        $this->factoryCollection = $factoryCollection;

        return $this;
    }

    /**
     * Get the factory collection
     *
     * @return FactoryCollection
     */
    public function getFactoryCollection(): FactoryCollection
    {
        return $this->factoryCollection;
    }

    /**
     * Get the PSR-7 request
     *
     * @return RequestInterface
     */
    public function createPsrRequest(): RequestInterface
    {
        $factories = $this->factoryCollection;

        $request = $factories->requestFactory->createRequest(
            method: $this->getMethod()->value,
            uri: $this->getUri(),
        );

        foreach ($this->headers()->all() as $headerName => $headerValue) {
            $request = $request->withHeader($headerName, $headerValue);
        }

        if ($this->body() instanceof BodyRepository) {
            $request = $request->withBody($this->body()->toStream($factories->streamFactory));
        }

        // Now we'll run our event hooks on both the connector and request which allows the
        // user to be able to make any final changes to the PSR request if they need to
        // like modifying the URI or adding extra headers.

        $request = $this->connector->handlePsrRequest($request, $this);

        return $this->request->handlePsrRequest($request, $this);
    }

    /**
     * Set the body repository
     *
     * @param \Saloon\Contracts\Body\BodyRepository|null $body
     * @return $this
     */
    public function setBody(?BodyRepository $body): static
    {
        $this->body = $body;

        return $this;
    }
}
