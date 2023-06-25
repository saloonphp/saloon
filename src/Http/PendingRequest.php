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
use Saloon\Contracts\Body\HasBody;
use Saloon\Contracts\FakeResponse;
use Saloon\Data\FactoryCollection;
use Saloon\Contracts\Authenticator;
use Saloon\Helpers\ReflectionHelper;
use Saloon\Http\Faking\MockResponse;
use Psr\Http\Message\RequestInterface;
use Saloon\Contracts\Body\MergeableBody;
use Saloon\Http\Middleware\DebugRequest;
use Saloon\Contracts\Body\BodyRepository;
use Saloon\Http\Middleware\DebugResponse;
use Saloon\Http\Middleware\DelayMiddleware;
use Saloon\Traits\Auth\AuthenticatesRequests;
use Saloon\Exceptions\PendingRequestException;
use Saloon\Http\Middleware\AuthenticateRequest;
use Saloon\Http\Middleware\DetermineMockResponse;
use Saloon\Contracts\Response as ResponseContract;
use Saloon\Exceptions\InvalidResponseClassException;
use Saloon\Repositories\Body\MultipartBodyRepository;
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
     * The request used by the instance.
     *
     * @var \Saloon\Contracts\Request
     */
    protected Request $request;

    /**
     * The connector making the request.
     *
     * @var \Saloon\Contracts\Connector
     */
    protected Connector $connector;

    /**
     * The factory collection.
     *
     * @var FactoryCollection
     */
    protected FactoryCollection $factoryCollection;

    /**
     * The URL the request will be made to.
     *
     * @var string
     */
    protected string $url;

    /**
     * The method the request will use.
     *
     * @var \Saloon\Enums\Method
     */
    protected Method $method;

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
     * Build up the request payload.
     *
     * @param \Saloon\Contracts\Connector $connector
     * @param \Saloon\Contracts\Request $request
     * @param \Saloon\Contracts\MockClient|null $mockClient
     * @throws \ReflectionException
     * @throws \Saloon\Exceptions\InvalidResponseClassException
     * @throws \Saloon\Exceptions\PendingRequestException
     */
    public function __construct(Connector $connector, Request $request, MockClient $mockClient = null)
    {
        $this->connector = $connector;
        $this->request = $request;
        $this->factoryCollection = $connector->sender()->getFactoryCollection();
        $this->url = $this->resolveRequestUrl();
        $this->method = $request->getMethod();
        $this->responseClass = $this->resolveResponseClass();
        $this->mockClient = $mockClient ?? $request->getMockClient() ?? $connector->getMockClient();
        $this->authenticator = $request->getAuthenticator() ?? $connector->getAuthenticator();

        // Todo for Sam: Change to the new middleware order and see what breaks 🤞

        $middleware = $this->middleware();

        $middleware->merge(Config::middleware());

        // New Middleware Order:
        // 1. Global Middleware (Laravel)
        // 2. Plugin Middleware (Rate Limiter)
        // 3. Authentication
        // 4. Mock Response
        // 5. User Middleware
        // 6. Debugging/Event/Delay Middleware

        // Delay should always be at the bottom in case the user adds their own
        // delay from inside of middleware.

        // Todo: Move everything else into middleware

        $this
            ->bootPlugins()
            ->mergeRequestProperties()
            ->mergeBody()
            ->mergeDelay();

        // Now we'll queue te delay middleware and authenticator middleware

        $middleware
            ->onRequest(new AuthenticateRequest, false, 'authenticateRequest')
            ->onRequest(new DetermineMockResponse, false, 'determineMockResponse');

        // Next, we'll merge in the connector/request middleware

        $middleware
            ->merge($connector->middleware())
            ->merge($request->middleware());

        // Now we'll boot the connector and request which may add more middleware

        $this->bootConnectorAndRequest();

        // Now we will register the last middleware. This is middleware that will
        // always run at the end no matter where it is placed in the chain. This
        // is great for things like debugging and events.

        $this->registerLastMiddleware();

        // Next, we will execute the request middleware pipeline which will
        // process any middleware added on the connector or the request.

        $this->executeRequestPipeline();
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

        $this->headers()->merge(
            $connector->headers()->all(),
            $request->headers()->all()
        );

        $this->query()->merge(
            $connector->query()->all(),
            $request->query()->all()
        );

        $this->config()->merge(
            $connector->config()->all(),
            $request->config()->all()
        );

        return $this;
    }

    /**
     * Merge the body together
     *
     * @return $this
     * @throws \Saloon\Exceptions\PendingRequestException
     */
    protected function mergeBody(): static
    {
        $connector = $this->connector;
        $request = $this->request;

        $connectorBody = $connector instanceof HasBody ? $connector->body() : null;
        $requestBody = $request instanceof HasBody ? $request->body() : null;

        if (is_null($connectorBody) && is_null($requestBody)) {
            return $this;
        }

        // When both the connector and the request use the `HasBody` interface - we will enforce
        // that they are both of the same type. This means there won't be any confusion when
        // merging.

        if (isset($connectorBody, $requestBody) && ! $connectorBody instanceof $requestBody) {
            throw new PendingRequestException('Connector and request body types must be the same.');
        }

        // We'll start by cloning the request or connector body depending on which
        // one has been set.

        $body = clone $requestBody ?? clone $connectorBody;

        // When both the connector and the request body repositories are mergeable then we
        // will merge them together.

        if (isset($connectorBody, $requestBody) && $connectorBody instanceof MergeableBody && $requestBody instanceof MergeableBody) {
            $repository = clone $connectorBody;

            // We'll clone the request body into the connector body so any properties on the request
            // body will take priority if they are using a keyed array.

            $body = $repository->merge($requestBody->all());
        }

        // Now we'll check if the body is a MultipartBodyRepository. If it is, then we must
        // set the body factory on the instance so the toStream method can create a stream
        // later on in the process.

        if ($body instanceof MultipartBodyRepository) {
            $body->setMultipartBodyFactory($this->factoryCollection->multipartBodyFactory);
        }

        $this->body = $body;

        return $this;
    }

    /**
     * Merge delay together
     *
     * Request delay takes priority over connector delay
     *
     * @return $this
     */
    protected function mergeDelay(): static
    {
        $this->request->delay()->isNotEmpty() ?
            $this->delay()->set($this->request->delay()->get()) :
            $this->delay()->set($this->connector->delay()->get());

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
     * Register any default middleware to run at the end of the middleware stack.
     *
     * @return $this
     */
    protected function registerLastMiddleware(): static
    {
        $middleware = $this->middleware();

        // Finally, we'll register the debugging middleware. This should always
        // stay at the bottom of the middleware chain, so we output the very
        // latest PendingRequest/Response

        $middleware->onRequest(new DelayMiddleware, false, 'delayMiddleware');

        $middleware->onRequest(new DebugRequest, false, 'debugRequest');

        $middleware->onResponse(new DebugResponse, false, 'debugResponse');

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
     * @param \Saloon\Contracts\Response $response
     * @return \Saloon\Contracts\Response
     */
    public function executeResponsePipeline(ResponseContract $response): ResponseContract
    {
        return $this->middleware()->executeResponsePipeline($response);
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

        if (! class_exists($response) || ! ReflectionHelper::isSubclassOf($response, ResponseContract::class)) {
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
}
