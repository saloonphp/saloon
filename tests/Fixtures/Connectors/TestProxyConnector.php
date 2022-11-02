<?php declare(strict_types=1);

namespace Sammyjo20\Saloon\Tests\Fixtures\Connectors;

use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Http\SaloonConnector;
use Sammyjo20\Saloon\Traits\Plugins\AcceptsJson;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\UserRequest;

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

    /**
     * Return a greeting!
     *
     * @return string
     */
    public function greeting(): string
    {
        return 'Howdy!';
    }
}
