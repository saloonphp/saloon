<?php

namespace Sammyjo20\Saloon\Tests\Resources\Connectors;

use Sammyjo20\Saloon\Http\SaloonConnector;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Tests\Resources\Requests\UserRequest;
use Sammyjo20\Saloon\Traits\Features\AcceptsJson;

class TestProxyConnector extends SaloonConnector
{
    use AcceptsJson;

    /**
     * Define the base url of the api.
     *
     * @return string
     */
    public function defineBaseUrl(): string
    {
        return apiUrl();
    }

    /**
     * Define the base headers that will be applied in every request.
     *
     * @return string[]
     */
    public function defaultHeaders(): array
    {
        return [];
    }

    /**
     * Get the user from the system.
     *
     * @param ...$args
     * @return SaloonRequest
     * @throws \ReflectionException
     */
    public function getUser(...$args): SaloonRequest
    {
        return $this->forwardCallToRequest(UserRequest::class, $args);
    }
}
