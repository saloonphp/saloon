<?php

namespace Sammyjo20\Saloon\Tests\Resources\Connectors;

use Sammyjo20\Saloon\Http\SaloonConnector;
use Sammyjo20\Saloon\Tests\Resources\Plugins\HasTestHandler;
use Sammyjo20\Saloon\Traits\Features\AcceptsJson;

class NoTrailingSlashConnector extends SaloonConnector
{
    use AcceptsJson;

    /**
     * Specify if Saloon should add a trailing slash to the base url.
     *
     * @var bool
     */
    public $addTrailingSlashToBaseUrl = false;

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
}
