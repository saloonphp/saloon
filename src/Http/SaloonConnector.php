<?php

namespace Sammyjo20\Saloon\Http;

use ReflectionClass;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Sammyjo20\Saloon\Traits\HasMake;
use Sammyjo20\Saloon\Traits\HasKeychain;
use Sammyjo20\Saloon\Traits\CollectsData;
use Sammyjo20\Saloon\Traits\CollectsConfig;
use Sammyjo20\Saloon\Traits\CollectsHeaders;
use Sammyjo20\Saloon\Traits\CollectsHandlers;
use Sammyjo20\Saloon\Helpers\ReflectionHelper;
use Sammyjo20\Saloon\Traits\HasCustomResponses;
use Sammyjo20\Saloon\Traits\CollectsQueryParams;
use Sammyjo20\Saloon\Traits\CollectsInterceptors;
use Sammyjo20\Saloon\Traits\AuthenticatesRequests;
use Sammyjo20\Saloon\Exceptions\ClassNotFoundException;
use Sammyjo20\Saloon\Interfaces\SaloonConnectorInterface;
use Sammyjo20\Saloon\Exceptions\SaloonInvalidRequestException;
use Sammyjo20\Saloon\Exceptions\SaloonConnectorMethodNotFoundException;

abstract class SaloonConnector implements SaloonConnectorInterface
{
    use CollectsHeaders,
        CollectsData,
        CollectsQueryParams,
        CollectsConfig,
        CollectsHandlers,
        CollectsInterceptors,
        AuthenticatesRequests,
        HasCustomResponses,
        HasMake;

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
     * @throws ClassNotFoundException
     * @throws SaloonInvalidRequestException
     * @throws \ReflectionException
     */
    protected function forwardCallToRequest(string $request, array $args = []): SaloonRequest
    {
        if (! class_exists($request)) {
            throw new ClassNotFoundException($request);
        }

        $isValidRequest = ReflectionHelper::isSubclassOf($request, SaloonRequest::class);

        if (! $isValidRequest) {
            throw new SaloonInvalidRequestException($request);
        }

        return (new $request(...$args))->setConnector($this);
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

        $requests = (new Collection($this->requests))->mapWithKeys(function ($value, $key) {
            if (is_string($key)) {
                return [$key => $value];
            }

            $guessedKey = Str::camel((new ReflectionClass($value))->getShortName());

            return [$guessedKey => $value];
        })->toArray();

        $this->registeredRequests = $requests;

        return $requests;
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
    public function __call($method, $arguments)
    {
        if ($this->requestExists($method) === false) {
            throw new SaloonConnectorMethodNotFoundException($method, $this);
        }

        $requests = $this->getRegisteredRequests();

        return $this->forwardCallToRequest($requests[$method], $arguments);
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
        $connector = new static;

        if ($connector->requestExists($method) === false) {
            throw new SaloonConnectorMethodNotFoundException($method, $connector);
        }

        $requests = $connector->getRegisteredRequests();

        return $connector->forwardCallToRequest($requests[$method], $arguments);
    }
}
