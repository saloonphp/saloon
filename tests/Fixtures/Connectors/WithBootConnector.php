<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\Request;
use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

class WithBootConnector extends Connector
{
    use AcceptsJson;

    /**
     * Define the base url of the api.
     */
    public function resolveBaseUrl(): string
    {
        return apiUrl();
    }

    public function boot(Request $request): void
    {
        $this->addHeader('X-Connector-Boot-Header', 'Howdy!');
        $this->addHeader('X-Connector-Request-Class', get_class($request));
    }
}
