<?php

namespace Sammyjo20\Saloon\Tests\Resources\Connectors;

use Sammyjo20\Saloon\Http\SaloonConnector;
use Sammyjo20\Saloon\Tests\Resources\Plugins\HasTestHandler;
use Sammyjo20\Saloon\Traits\Features\AcceptsJson;

class WithBootConnector extends SaloonConnector
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

    public function boot(): void
    {
        $this->addHeader('X-Connector-Boot-Header', 'Howdy!');
    }
}
