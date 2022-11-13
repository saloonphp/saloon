<?php declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\SaloonConnector;
use Saloon\Traits\Plugins\AcceptsJson;

class DifferentServiceConnector extends SaloonConnector
{
    use AcceptsJson;

    /**
     * Define the base url of the api.
     *
     * @return string
     */
    public function defineBaseUrl(): string
    {
        return 'https://google.com';
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
}
