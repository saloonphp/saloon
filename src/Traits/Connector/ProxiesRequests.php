<?php declare(strict_types=1);

namespace Saloon\Traits\Connector;

use Saloon\Helpers\RequestHelper;
use Saloon\Helpers\ReflectionHelper;
use Saloon\Http\Groups\RequestGroup;
use Saloon\Helpers\ProxyRequestNameHelper;
use Saloon\Exceptions\ClassNotFoundException;
use Saloon\Http\Groups\AnonymousRequestGroup;
use Saloon\Exceptions\InvalidRequestException;
use Saloon\Exceptions\ConnectorMethodNotFoundException;

trait ProxiesRequests
{
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
     * Check if a given request method exists
     *
     * @param string $method
     * @return bool
     * @throws \ReflectionException
     */
    protected function requestExists(string $method): bool
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
     * @throws ConnectorMethodNotFoundException
     * @throws InvalidRequestException
     * @throws \ReflectionException
     */
    protected function proxyRequest($method, $arguments): mixed
    {
        if ($this->requestExists($method) === false) {
            throw new ConnectorMethodNotFoundException($method, $this);
        }

        $requests = $this->getRegisteredRequests();

        // Work out what it is. If it is an array, pass the array into AnonymousRequestCollection($requests)
        // If it is a request, just forward the call to the request.

        $resource = $requests[$method];

        // If the request is a type of array, then it must be an anonymous request collection.

        if (is_array($resource)) {
            return new AnonymousRequestGroup($this, $method, $resource);
        }

        // Otherwise, check if it is a RequestCollection. If it is, then
        // return that class - otherwise, just forward the request.

        if (! class_exists($resource)) {
            throw new ClassNotFoundException($resource);
        }

        if (ReflectionHelper::isSubclassOf($resource, RequestGroup::class)) {
            return new $resource($this);
        }

        // It's just a request, so forward to that.

        return RequestHelper::callFromConnector($this, $resource, $arguments);
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
}
