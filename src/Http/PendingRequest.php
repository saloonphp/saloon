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
use Saloon\Contracts\FakeResponse;
use Saloon\Contracts\Authenticator;
use Saloon\Http\Middleware\MergeBody;
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
use Saloon\Traits\PendingRequest\ManagesPsrRequests;
use Saloon\Traits\RequestProperties\HasRequestProperties;
use Saloon\Contracts\PendingRequest as PendingRequestContract;

class PendingRequest implements PendingRequestContract
{
    use AuthenticatesRequests;
    use HasRequestProperties;
    use ManagesPsrRequests;
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
        // Let's start by getting our PSR factory collection. This object contains all the
        // relevant factories for creating PSR-7 requests as well as URIs and streams.

        $this->factoryCollection = $connector->sender()->getFactoryCollection();

        // Now we'll set the base properties

        $this->connector = $connector;
        $this->request = $request;
        $this->method = $request->getMethod();
        $this->url = URLHelper::join($this->connector->resolveBaseUrl(), $this->request->resolveEndpoint());
        $this->authenticator = $request->getAuthenticator() ?? $connector->getAuthenticator();
        $this->mockClient = $mockClient ?? $request->getMockClient() ?? $connector->getMockClient();

        // Next, we'll boot our plugin traits.

        $this->bootPlugins();

        // Finally, we'll register and execute the middleware pipeline.

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
     */
    protected function registerAndExecuteMiddleware(): void
    {
        $middleware = $this->middleware();

        // We'll start with our core middleware like merging request properties, merging the
        // body, delay and also running authenticators on the request.

        $middleware
            ->onRequest(new MergeRequestProperties, 'mergeRequestProperties')
            ->onRequest(new MergeBody, 'mergeBody')
            ->onRequest(new MergeDelay, 'mergeDelay')
            ->onRequest(new AuthenticateRequest, 'authenticateRequest')
            ->onRequest(new DetermineMockResponse, 'determineMockResponse');

        // Next, we'll merge in our "Global" middleware which can be middleware set by the
        // user or set by Saloon's plugins like the Laravel Plugin. It's best that this
        // middleware is run now because we want the user to still have an opportunity
        // to overwrite anything applied by it.

        $middleware->merge(Config::middleware());

        // Now we'll "boot" the connector and request. This is a hook that can be run after
        // the core middleware that allows you to add your own properties that are a higher
        // priority than anything else.

        $this->bootConnectorAndRequest();

        // Now we'll merge the middleware added on the connector and the request. This
        // middleware will have almost the final object to play with and overwrite if
        // they desire.

        $middleware
            ->merge($this->connector->middleware())
            ->merge($this->request->middleware());

        // Next, we'll delay the request if we need to. This will run before the final
        // middleware.

        $middleware->onRequest(new DelayMiddleware, 'delayMiddleware');

        // Finally, we'll apply our "final" middleware. This is a group of middleware
        // that will run at the end, no matter what. This is useful for debugging and
        // events where we can guarantee that the middleware will be run at the end.

        $middleware
            ->onRequest(new DebugRequest, 'debugRequest')
            ->onResponse(new DebugResponse, 'debugResponse');

        // Next, we will execute the request middleware pipeline which will
        // process the middleware in the order we added it.

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
