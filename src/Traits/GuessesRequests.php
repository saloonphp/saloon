<?php

namespace Sammyjo20\Saloon\Traits;

use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Helpers\RequestHelper;
use Sammyjo20\Saloon\Http\RequestCollection;
use Sammyjo20\Saloon\Helpers\ReflectionHelper;
use Sammyjo20\Saloon\Helpers\ProxyRequestNameHelper;
use Sammyjo20\Saloon\Http\AnonymousRequestCollection;
use Sammyjo20\Saloon\Exceptions\ClassNotFoundException;
use Sammyjo20\Saloon\Exceptions\SaloonInvalidRequestException;
use Sammyjo20\Saloon\Exceptions\SaloonConnectorMethodNotFoundException;

trait GuessesRequests
{
    /**
     * Requests that have already been registered. Used as a cache for performance.
     *
     * @var array|null
     */
    private ?array $registeredRequests = null;

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
