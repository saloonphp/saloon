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
use Saloon\Traits\Conditionable;
use Saloon\Traits\HasMockClient;
use Psr\Http\Message\UriInterface;
use Saloon\Contracts\FakeResponse;
use Saloon\Data\FactoryCollection;
use Saloon\Contracts\Authenticator;
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
     */
    protected Connector $connector;

    /**
     * The request used by the instance.
     */
    protected Request $request;

    /**
     * The method the request will use.
     */
    protected Method $method;

    /**
     * The URL the request will be made to.
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
     */
    protected ?BodyRepository $body = null;

    /**
     * The simulated response.
     */
    protected ?FakeResponse $fakeResponseData = null;

    /**
     * Determine if the pending request is asynchronous
     */
    protected bool $asynchronous = false;

    /**
     * The factory collection.
     */
    protected FactoryCollection $factoryCollection;

    /**
     * Build up the request payload.
     *
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
     */
    public function getRequest(): Request
    {
        return $this->request;
    }

    /**
     * Execute the response pipeline.
     */
    public function executeResponsePipeline(ResponseContract $response): ResponseContract
    {
        return $this->middleware()->executeResponsePipeline($response);
    }


    /**
     * Get the connector.
     */
    public function getConnector(): Connector
    {
        return $this->connector;
    }

    /**
     * Get the URL of the request.
     */
    public function getUrl(): string
    {
        return $this->url;
    }

    /**
     * Get the URI for the pending request.
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
     */
    public function body(): ?BodyRepository
    {
        return $this->body;
    }

    /**
     * Get the simulated response payload
     */
    public function getFakeResponse(): ?FakeResponse
    {
        return $this->fakeResponseData;
    }

    /**
     * Set the simulated response payload
     *
     * @return $this
     */
    public function setFakeResponse(?FakeResponse $fakeResponse): static
    {
        $this->fakeResponseData = $fakeResponse;

        return $this;
    }

    /**
     * Check if simulated response payload is present.
     */
    public function hasFakeResponse(): bool
    {
        return $this->fakeResponseData instanceof FakeResponse;
    }

    /**
     * Build up the full request URL.
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
     */
    public function createDtoFromResponse(ResponseContract $response): mixed
    {
        return $this->request->createDtoFromResponse($response) ?? $this->connector->createDtoFromResponse($response);
    }

    /**
     * Set if the request is going to be sent asynchronously
     *
     * @return $this
     */
    public function setAsynchronous(bool $asynchronous): static
    {
        $this->asynchronous = $asynchronous;

        return $this;
    }

    /**
     * Check if the request is asynchronous
     */
    public function isAsynchronous(): bool
    {
        return $this->asynchronous;
    }

    /**
     * Authenticate the PendingRequest
     *
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
     * @return $this
     */
    public function setFactoryCollection(FactoryCollection $factoryCollection): static
    {
        $this->factoryCollection = $factoryCollection;

        return $this;
    }

    /**
     * Get the factory collection
     */
    public function getFactoryCollection(): FactoryCollection
    {
        return $this->factoryCollection;
    }

    /**
     * Get the PSR-7 request
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
     * @return $this
     */
    public function setBody(?BodyRepository $body): static
    {
        $this->body = $body;

        return $this;
    }
}
