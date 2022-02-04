<?php

namespace Sammyjo20\Saloon\Http;

use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Sammyjo20\Saloon\Exceptions\ClassNotFoundException;
use Sammyjo20\Saloon\Exceptions\SaloonInvalidRequestException;
use Sammyjo20\Saloon\Exceptions\SaloonMethodNotFoundException;
use Sammyjo20\Saloon\Traits\CollectsData;
use Sammyjo20\Saloon\Traits\CollectsConfig;
use Sammyjo20\Saloon\Traits\CollectsHeaders;
use Sammyjo20\Saloon\Traits\CollectsHandlers;
use Sammyjo20\Saloon\Traits\HasCustomResponses;
use Sammyjo20\Saloon\Traits\CollectsQueryParams;
use Sammyjo20\Saloon\Traits\CollectsInterceptors;
use Sammyjo20\Saloon\Interfaces\SaloonConnectorInterface;
use ReflectionClass;

abstract class SaloonConnector implements SaloonConnectorInterface
{
    use CollectsHeaders,
        CollectsData,
        CollectsQueryParams,
        CollectsConfig,
        CollectsHandlers,
        CollectsInterceptors,
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
     * Attempt to create a request and forward parameters to it.
     *
     * @param string $request
     * @param array $args
     * @return SaloonRequest
     * @throws ClassNotFoundException
     * @throws SaloonInvalidRequestException
     */
    protected function forwardCallToRequest(string $request, array $args = []): SaloonRequest
    {
        if (! class_exists($request)) {
            throw new ClassNotFoundException($request);
        }

        $isValidRequest = (new ReflectionClass($request))->isSubclassOf(SaloonRequest::class);

        if (! $isValidRequest) {
            throw new SaloonInvalidRequestException($request);
        }

        return (new $request(...$args))->setLoadedConnector($this);
    }

    /**
     * Bootstrap the registered requests in the $requests array.
     *
     * @return array
     * @throws \ReflectionException
     */
    private function registerRequests(): array
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
     * Dynamically proxy other methods to try and call a requests.
     *
     * @param $method
     * @param $arguments
     * @return SaloonRequest
     * @throws ClassNotFoundException
     * @throws SaloonInvalidRequestException
     * @throws SaloonMethodNotFoundException
     * @throws \ReflectionException
     */
    public function __call($method, $arguments)
    {
        $requests = $this->registerRequests();

        if (array_key_exists($method, $requests) === false) {
            throw new SaloonMethodNotFoundException($method, $this);
        }

        $request = $requests[$method];

        return $this->forwardCallToRequest($request, ...$arguments);
    }

    /**
     * Define anything to be added to the connector.
     *
     * @return void
     */
    public function boot(): void
    {
        // TODO: Implement boot() method.
    }
}
