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
     * Manually specify requests that the connector will register as methods
     *
     * @var array|string[]
     */
    protected array $requests = [];

    /**
     * The loaded requests.
     *
     * @var array|null
     */
    private ?array $loadedRequests = null;

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
     * Get the available requests on the connector.
     *
     * @return array
     * @throws \ReflectionException
     */
    private function getAvailableRequests(): array
    {
        if (is_array($this->loadedRequests)) {
            return $this->loadedRequests;
        }

        $loadedRequests = (new Collection($this->requests))->mapWithKeys(function ($value, $key) {
            if (is_string($key)) {
                return [$key => $value];
            }

            $guessedKey = Str::camel((new ReflectionClass($value))->getShortName());

            return [$guessedKey => $value];
        })->toArray();

        $this->loadedRequests = $loadedRequests;

        return $loadedRequests;
    }

    /**
     * Dynamically proxy other methods to try and call a requests.
     *
     * @param $method
     * @param $arguments
     * @return SaloonRequest
     * @throws SaloonMethodNotFoundException
     * @throws \ReflectionException
     */
    public function __call($method, $arguments)
    {
        $requests = $this->getAvailableRequests();

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
