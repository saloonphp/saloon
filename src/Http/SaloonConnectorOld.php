<?php

namespace Sammyjo20\Saloon\Http;

use Illuminate\Support\Collection;
use GuzzleHttp\Promise\PromiseInterface;
use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Traits\CollectsData;
use Sammyjo20\Saloon\Helpers\RequestHelper;
use Sammyjo20\Saloon\Traits\CollectsConfig;
use Sammyjo20\Saloon\Traits\CollectsHeaders;
use Sammyjo20\Saloon\Traits\CollectsHandlers;
use Sammyjo20\Saloon\Helpers\ReflectionHelper;
use Sammyjo20\Saloon\Traits\HasCustomResponses;
use Sammyjo20\Saloon\Traits\CollectsQueryParams;
use Sammyjo20\Saloon\Traits\CollectsInterceptors;
use Sammyjo20\Saloon\Traits\AuthenticatesRequests;
use Sammyjo20\Saloon\Helpers\ProxyRequestNameHelper;
use Sammyjo20\Saloon\Exceptions\ClassNotFoundException;
use Sammyjo20\Saloon\Interfaces\SaloonConnectorInterface;
use Sammyjo20\Saloon\Exceptions\SaloonInvalidRequestException;
use Sammyjo20\Saloon\Exceptions\SaloonConnectorMethodNotFoundException;

abstract class SaloonConnectorOld implements SaloonConnectorInterface
{
    use CollectsHeaders,
        CollectsData,
        CollectsQueryParams,
        CollectsConfig,
        CollectsHandlers,
        CollectsInterceptors,
        AuthenticatesRequests,
        HasCustomResponses;

    /**
     * Register Saloon requests that will become methods on the connector.
     * For example, GetUserRequest would become $this->getUserRequest(...$args)
     *
     * @var array|string[]
     */
    protected array $requests = [];

    /**
     * Requests that have already been registered. Used as a cache for performance.
     *
     * @var array|null
     */
    private ?array $registeredRequests = null;

    /**
     * Instantiate a new class with the arguments.
     *
     * @param mixed ...$arguments
     * @return SaloonConnector
     */
    public static function make(...$arguments): self
    {
        return new static(...$arguments);
    }

    /**
     * Define anything that should be added to any requests
     * with this connector before the request is sent.
     *
     * @param SaloonRequest $request
     * @return void
     */
    public function boot(SaloonRequest $request): void
    {
        //
    }

    /**
     * Attempt to create a request and forward parameters to it.
     *
     * @param string $request
     * @param array $args
     * @return SaloonRequest
     * @throws SaloonInvalidRequestException
     * @throws \ReflectionException
     */
    protected function forwardCallToRequest(string $request, array $args = []): SaloonRequest
    {
        return RequestHelper::callFromConnector($this, $request, $args);
    }

    /**
     * Bootstrap and get the registered requests in the $requests array.
     *
     * @return array
     * @throws \ReflectionException
     */
    public function getRegisteredRequests(): array
    {
        if (empty($this->requests)) {
            return [];
        }

        if (is_array($this->registeredRequests)) {
            return $this->registeredRequests;
        }

        $this->registeredRequests = ProxyRequestNameHelper::generateNames($this->requests);

        return $this->registeredRequests;
    }

    /**
     * Check if a given request method exists
     *
     * @param string $method
     * @return bool
     * @throws \ReflectionException
     */
    public function requestExists(string $method): bool
    {
        return method_exists($this, $method) || array_key_exists($method, $this->getRegisteredRequests());
    }

    /**
     * Prepare a new request by providing it the current instance of the connector.
     *
     * @param SaloonRequest $request
     * @return SaloonRequest
     */
    public function request(SaloonRequest $request): SaloonRequest
    {
        return $request->setConnector($this);
    }

    /**
     * Send a Saloon request with the current instance of the connector.
     *
     * @throws \ReflectionException
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonException
     */
    public function send(SaloonRequest $request, MockClient $mockClient = null): SaloonResponse
    {
        return $this->request($request)->send($mockClient);
    }

    /**
     * Send an asynchronous Saloon request with the current instance of the connector.
     *
     * @param SaloonRequest $request
     * @param MockClient|null $mockClient
     * @return PromiseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \ReflectionException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonException
     */
    public function sendAsync(SaloonRequest $request, MockClient $mockClient = null): PromiseInterface
    {
        return $this->request($request)->sendAsync($mockClient);
    }

    /**
     * Dynamically proxy other methods to try and call a requests.
     *
     * @param $method
     * @param $arguments
     * @return AnonymousRequestCollection|SaloonRequest
     * @throws ClassNotFoundException
     * @throws SaloonConnectorMethodNotFoundException
     * @throws SaloonInvalidRequestException
     * @throws \ReflectionException
     */
    public function __call($method, $arguments)
    {
        return $this->guessRequest($method, $arguments);
    }

    /**
     * Dynamically proxy other methods to try and call a requests.
     *
     * @param $method
     * @param $arguments
     * @return SaloonRequest
     * @throws ClassNotFoundException
     * @throws SaloonInvalidRequestException
     * @throws SaloonConnectorMethodNotFoundException
     * @throws \ReflectionException
     */
    public static function __callStatic($method, $arguments)
    {
        return (new static)->guessRequest($method, $arguments);
    }

    /**
     * Attempt to guess the next request.
     *
     * @param $method
     * @param $arguments
     * @return mixed
     * @throws ClassNotFoundException
     * @throws SaloonConnectorMethodNotFoundException
     * @throws SaloonInvalidRequestException
     * @throws \ReflectionException
     */
    protected function guessRequest($method, $arguments): mixed
    {
        if ($this->requestExists($method) === false) {
            throw new SaloonConnectorMethodNotFoundException($method, $this);
        }

        $requests = $this->getRegisteredRequests();

        // Work out what it is. If it is an array, pass the array into AnonymousRequestCollection($requests)
        // If it is a request, just forward the call to the request.

        $resource = $requests[$method];

        // If the request is a type of array, then it must be an anonymous request collection.

        if (is_array($resource)) {
            return new AnonymousRequestCollection($this, $method, $resource);
        }

        // Otherwise, check if it is a RequestCollection. If it is, then
        // return that class - otherwise, just forward the request.

        if (! class_exists($resource)) {
            throw new ClassNotFoundException($resource);
        }

        if (ReflectionHelper::isSubclassOf($resource, RequestCollection::class)) {
            return new $resource($this);
        }

        // It's just a request, so forward to that.

        return $this->forwardCallToRequest($resource, $arguments);
    }
}
