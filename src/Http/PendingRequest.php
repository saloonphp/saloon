<?php

declare(strict_types=1);

namespace Saloon\Http;

use Saloon\Enums\Method;
use Saloon\Helpers\Helpers;
use Saloon\Contracts\Sender;
use Saloon\Contracts\Request;
use Saloon\Contracts\Response;
use Saloon\Contracts\Connector;
use Saloon\Helpers\Environment;
use Saloon\Contracts\MockClient;
use Saloon\Helpers\PluginHelper;
use Saloon\Traits\Conditionable;
use Saloon\Traits\HasMockClient;
use Saloon\Contracts\Body\WithBody;
use GuzzleHttp\Promise\PromiseInterface;
use Saloon\Contracts\Body\BodyRepository;
use Saloon\Traits\Auth\AuthenticatesRequests;
use Saloon\Exceptions\PendingRequestException;
use Saloon\Http\Middleware\AuthenticateRequest;
use Saloon\Http\Faking\SimulatedResponsePayload;
use Saloon\Http\Middleware\DetermineMockResponse;
use Saloon\Repositories\Body\ArrayBodyRepository;
use Saloon\Laravel\Http\Middleware\FrameworkMiddleware;
use Saloon\Traits\RequestProperties\HasRequestProperties;
use Saloon\Contracts\PendingRequest as PendingRequestContract;

/**
 * @template TRequest of \Saloon\Contracts\Request
 *
 * @implements \Saloon\Contracts\PendingRequest<TRequest>
 */
class PendingRequest implements PendingRequestContract
{
    use AuthenticatesRequests;
    use HasRequestProperties;
    use Conditionable;
    use HasMockClient;

    /**
     * The request used by the instance.
     *
     * @var TRequest
     */
    protected Request $request;

    /**
     * The connector making the request.
     *
     * @var \Saloon\Contracts\Connector
     */
    protected Connector $connector;

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
     * @var string
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
     * @var SimulatedResponsePayload|null
     */
    protected ?SimulatedResponsePayload $simulatedResponsePayload = null;

    /**
     * Build up the request payload.
     *
     * @param TRequest $request
     * @param \Saloon\Contracts\MockClient|null $mockClient
     * @throws \ReflectionException
     * @throws \Saloon\Exceptions\PendingRequestException
     */
    public function __construct(Request $request, MockClient $mockClient = null)
    {
        $connector = $request->connector();

        $this->request = $request;
        $this->connector = $connector;
        $this->url = $request->getRequestUrl();
        $this->method = Method::upperFrom($request->getMethod());
        $this->responseClass = $request->getResponseClass();
        $this->mockClient = $mockClient ?? ($request->getMockClient() ?? $connector->getMockClient());
        $this->authenticator = $request->getAuthenticator() ?? $connector->getAuthenticator();

        // After we have defined each of our properties, we will run the various
        // methods that build up the PendingRequest. It's important that
        // the order remains the same.

        // Plugins should be booted first, then we will merge the properties
        // from the connector and request, then authenticate the request
        // followed by finally running the "boot" method with an
        // almost complete PendingRequest.

        $this->bootPlugins()
            ->mergeRequestProperties()
            ->mergeBody()
            ->bootConnectorAndRequest();

        // Now we will register the default middleware. The user's defined
        // middleware will come first, and then we will process the
        // default middleware.

        $this->registerDefaultMiddleware();

        // Finally, we will execute the request middleware pipeline which will
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

        $this->middleware()
            ->merge($connector->middleware())
            ->merge($request->middleware());

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

        $connectorBody = $connector instanceof WithBody ? $connector->body() : null;
        $requestBody = $request instanceof WithBody ? $request->body() : null;

        if (is_null($connectorBody) && is_null($requestBody)) {
            return $this;
        }

        if (isset($connectorBody, $requestBody) && ! $connectorBody instanceof $requestBody) {
            throw new PendingRequestException('Connector and request body types must be the same.');
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
    protected function registerDefaultMiddleware(): static
    {
        $middleware = $this->middleware();

        // We're going to register the internal middleware that should be run before
        // a request is sent. This order should remain exactly the same.

        $middleware->onRequest(new AuthenticateRequest);

        // Next we will check if we are in a Laravel environment and if we have the
        // Laravel middleware. If we do then Laravel can make changes to the
        // request like add its MockClient and record requests.

        if (Environment::detectsLaravel() && class_exists(FrameworkMiddleware::class)) {
            $middleware->onRequest(new FrameworkMiddleware);
        }

        // Next we will run the MockClient and determine if we should send a real
        // request or not. Keep DetermineMockResponse at the bottom so other
        // middleware can set the MockClient before we run the MockResponse.

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
     * @param \Saloon\Contracts\Response $response
     * @return \Saloon\Contracts\Response
     */
    public function executeResponsePipeline(Response $response): Response
    {
        $this->middleware()->executeResponsePipeline($response);

        return $response;
    }

    /**
     * Get the request.
     *
     * @return TRequest
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
     * @return string
     */
    public function getResponseClass(): string
    {
        return $this->responseClass;
    }

    /**
     * Get the request sender.
     *
     * @return \Saloon\Contracts\Sender
     */
    public function getSender(): Sender
    {
        return $this->connector->sender();
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
     * @return \Saloon\Http\Faking\SimulatedResponsePayload|null
     */
    public function getSimulatedResponsePayload(): ?SimulatedResponsePayload
    {
        return $this->simulatedResponsePayload;
    }

    /**
     * Set the simulated response payload
     *
     * @param \Saloon\Http\Faking\SimulatedResponsePayload|null $simulatedResponsePayload
     * @return $this
     */
    public function setSimulatedResponsePayload(?SimulatedResponsePayload $simulatedResponsePayload): static
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
     * Send the PendingRequest
     *
     * @return \Saloon\Contracts\Response<TRequest>
     */
    public function send(): Response
    {
        return (new Dispatcher($this))->execute();
    }

    /**
     * Send the PendingRequest asynchronously
     *
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function sendAsync(): PromiseInterface
    {
        return (new Dispatcher($this, true))->execute();
    }
}
