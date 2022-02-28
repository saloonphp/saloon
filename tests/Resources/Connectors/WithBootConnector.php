<?php

namespace Sammyjo20\Saloon\Tests\Resources\Connectors;

use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Http\SaloonConnector;
use Sammyjo20\Saloon\Traits\Plugins\AcceptsJson;

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

    public function boot(SaloonRequest $request): void
    {
        $this->addHeader('X-Connector-Boot-Header', 'Howdy!');
        $this->addHeader('X-Connector-Request-Class', get_class($request));
    }
}
