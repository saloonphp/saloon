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
use Saloon\Http\Middleware\InvokeDeferredAuthenticators;
use Saloon\Http\Middleware\DetermineMockResponse;
use Saloon\Contracts\Response as ResponseContract;
use Saloon\Http\Middleware\MergeRequestProperties;
use Saloon\Exceptions\InvalidResponseClassException;
use Saloon\Traits\PendingRequest\CreatesFakeResponses;
use Saloon\Traits\PendingRequest\ManagesPsrRequests;
use Saloon\Traits\RequestProperties\HasRequestProperties;
use Saloon\Contracts\PendingRequest as PendingRequestContract;

class PendingRequest implements PendingRequestContract
{
    use AuthenticatesRequests;
    use HasRequestProperties;
    use CreatesFakeResponses;
    use Conditionable;
    use HasMockClient;
    use ManagesPsrRequests;

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
     * The body of the request.
     */
    protected ?BodyRepository $body = null;

    /**
     * The simulated response.
     */
    protected ?FakeResponse $fakeResponse = null;

    /**
     * Determine if the pending request is asynchronous
     */
    protected bool $asynchronous = false;

    /**
     * Build up the request payload.
     *
     * @throws \ReflectionException
     */
    public function __construct(Connector $connector, Request $request, MockClient $mockClient = null)
    {
        $this->factoryCollection = $connector->sender()->getFactoryCollection();

        $this->connector = $connector;
        $this->request = $request;
        $this->method = $request->getMethod();
        $this->url = URLHelper::join($this->connector->resolveBaseUrl(), $this->request->resolveEndpoint());
        $this->authenticator = $request->getAuthenticator() ?? $connector->getAuthenticator();
        $this->mockClient = $mockClient ?? $request->getMockClient() ?? $connector->getMockClient();

        $this->bootPlugins();

        $this->registerAndExecuteMiddleware();
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
     * Register and execute middleware
     *
     * @return void
     */
    protected function registerAndExecuteMiddleware(): void
    {
        $middleware = $this->middleware();

        // New Middleware Order:

        // 1. Global (Laravel)
        // 2. Plugin (Rate Limiter)
        // 3. Deferred Authentication
        // 4. Mock Response
        // 5. User
        // 6. Delay/Debugging/Event

        $middleware->merge(Config::middleware());

        // Now we'll queue te delay middleware and authenticator middleware

        $middleware
            ->onRequest(new MergeRequestProperties, false, 'mergeRequestProperties')
            ->onRequest(new MergeBody, false, 'mergeBody')
            ->onRequest(new MergeDelay, false, 'mergeDelay')
            ->onRequest(new InvokeDeferredAuthenticators, false, 'invokeDeferredAuthenticators')
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
     * Execute the response pipeline.
     */
    public function executeResponsePipeline(ResponseContract $response): ResponseContract
    {
        return $this->middleware()->executeResponsePipeline($response);
    }

    /**
     * Get the request.
     */
    public function getRequest(): Request
    {
        return $this->request;
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
     * Set the URL of the PendingRequest
     *
     * Note: This will be combined with the query parameters to create
     * a UriInterface that will be passed to a PSR-7 request.
     *
     * @return $this
     */
    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    /**
     * Get the HTTP method used for the request
     */
    public function getMethod(): Method
    {
        return $this->method;
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
     * Retrieve the body on the instance
     */
    public function body(): ?BodyRepository
    {
        return $this->body;
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

    /**
     * Get the fake response
     */
    public function getFakeResponse(): ?FakeResponse
    {
        return $this->fakeResponse;
    }

    /**
     * Check if a fake response has been set
     */
    public function hasFakeResponse(): bool
    {
        return $this->fakeResponse instanceof FakeResponse;
    }

    /**
     * Set the fake response
     *
     * @return $this
     */
    public function setFakeResponse(?FakeResponse $fakeResponse): static
    {
        $this->fakeResponse = $fakeResponse;

        return $this;
    }

    /**
     * Get the response class
     *
     * @return class-string<\Saloon\Contracts\Response>
     * @throws \ReflectionException
     * @throws \Saloon\Exceptions\InvalidResponseClassException
     */
    public function getResponseClass(): string
    {
        $response = $this->request->resolveResponseClass() ?? $this->connector->resolveResponseClass() ?? Response::class;

        if (! class_exists($response) || ! Helpers::isSubclassOf($response, ResponseContract::class)) {
            throw new InvalidResponseClassException;
        }

        return $response;
    }

    /**
     * Check if the request is asynchronous
     */
    public function isAsynchronous(): bool
    {
        return $this->asynchronous;
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
}
