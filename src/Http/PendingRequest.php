<?php

declare(strict_types=1);

namespace Saloon\Http;

use Saloon\Config;
use Saloon\Enums\Method;
use Saloon\Helpers\Helpers;
use Saloon\Traits\Macroable;
use Saloon\Helpers\URLHelper;
use Saloon\Traits\Conditionable;
use Saloon\Traits\HasMockClient;
use Saloon\Contracts\FakeResponse;
use Saloon\Http\Faking\MockClient;
use Saloon\Contracts\Authenticator;
use Saloon\Contracts\Body\BodyRepository;
use Saloon\Http\PendingRequest\MergeBody;
use Saloon\Http\PendingRequest\MergeDelay;
use Saloon\Http\Middleware\DelayMiddleware;
use Saloon\Http\PendingRequest\BootPlugins;
use Saloon\Traits\Auth\AuthenticatesRequests;
use Saloon\Http\Middleware\ValidateProperties;
use Saloon\Http\Middleware\DetermineMockResponse;
use Saloon\Exceptions\InvalidResponseClassException;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Traits\PendingRequest\ManagesPsrRequests;
use Saloon\Http\PendingRequest\MergeRequestProperties;
use Saloon\Http\PendingRequest\BootConnectorAndRequest;
use Saloon\Traits\RequestProperties\HasRequestProperties;
use Saloon\Http\PendingRequest\AuthenticatePendingRequest;

class PendingRequest
{
    use AuthenticatesRequests;
    use HasRequestProperties;
    use ManagesPsrRequests;
    use Conditionable;
    use HasMockClient;
    use Macroable;

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
     */
    public function __construct(Connector $connector, Request $request, ?MockClient $mockClient = null)
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
        $this->mockClient = $mockClient ?? $request->getMockClient() ?? $connector->getMockClient() ?? MockClient::getGlobal();

        // Now, we'll register our global middleware and our mock response middleware.
        // Registering these middleware first means that the mock client can set
        // the fake response for every subsequent middleware.

        $this->middleware()->merge(Config::globalMiddleware());
        $this->middleware()->onRequest(new DetermineMockResponse, 'determineMockResponse');

        // Next, we'll boot our plugins. These plugins can add headers, config variables and
        // even register their own middleware. We'll use a tap method to simply apply logic
        // to the PendingRequest. After that, we will merge together our request properties
        // like headers, config, middleware, body and delay, and we'll follow it up by
        // invoking our authenticators. We'll do this here because when middleware is
        // executed, the developer will have access to any headers added by the middleware.

        $this
            ->tap(new BootPlugins)
            ->tap(new MergeRequestProperties)
            ->tap(new MergeBody)
            ->tap(new MergeDelay)
            ->tap(new AuthenticatePendingRequest)
            ->tap(new BootConnectorAndRequest);

        // Now, we'll register some default middleware for validating the request properties and
        // running the delay that should have been set by the user.

        $this->middleware()
            ->onRequest(new ValidateProperties, 'validateProperties')
            ->onRequest(new DelayMiddleware, 'delayMiddleware');

        // Finally, we will execute the request middleware pipeline which will
        // process the middleware in the order we added it.

        $this->middleware()->executeRequestPipeline($this);
    }

    /**
     * Authenticate the PendingRequest
     *
     * @return $this
     */
    public function authenticate(Authenticator $authenticator): static
    {
        $this->authenticator = $authenticator;

        // Since the PendingRequest has already been constructed we will run the set
        // method on the authenticator which runs it straight away.

        $this->authenticator->set($this);

        return $this;
    }

    /**
     * Execute the response pipeline.
     */
    public function executeResponsePipeline(Response $response): Response
    {
        return $this->middleware()->executeResponsePipeline($response);
    }

    /**
     * Execute the fatal pipeline.
     */
    public function executeFatalPipeline(FatalRequestException $throwable): void
    {
        $this->middleware()->executeFatalPipeline($throwable);
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
     * Check if a fake response has been set
     */
    public function hasFakeResponse(): bool
    {
        return $this->fakeResponse instanceof FakeResponse;
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

    /**
     * Get the response class
     *
     * @return class-string<\Saloon\Http\Response>
     * @throws \Saloon\Exceptions\InvalidResponseClassException
     */
    public function getResponseClass(): string
    {
        $response = $this->request->resolveResponseClass() ?? $this->connector->resolveResponseClass() ?? Response::class;

        if (! class_exists($response) || ! Helpers::isSubclassOf($response, Response::class)) {
            throw new InvalidResponseClassException;
        }

        return $response;
    }

    /**
     * Tap into the pending request
     *
     * @return $this
     */
    protected function tap(callable $callable): static
    {
        $callable($this);

        return $this;
    }
}
