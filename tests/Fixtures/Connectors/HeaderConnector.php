<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

class HeaderConnector extends Connector
{
    use AcceptsJson;

    public function resolveBaseUrl(): string
    {
        return apiUrl();
    }

    public function defaultHeaders(): array
    {
        return [
            'X-Connector-Header' => 'Sam',
        ];
    }

    public function defaultConfig(): array
    {
        return [
            'http_errors' => false,
        ];
    }
}
