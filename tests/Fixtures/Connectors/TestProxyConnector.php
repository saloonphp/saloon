<?php declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\Request;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Tests\Fixtures\Requests\UserRequest;

class TestProxyConnector extends Connector
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
     * @return Request
     * @throws \ReflectionException
     */
    public function getUser(...$args): Request
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
