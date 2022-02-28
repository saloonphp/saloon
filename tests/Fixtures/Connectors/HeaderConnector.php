<?php

namespace Sammyjo20\Saloon\Tests\Fixtures\Connectors;

use Sammyjo20\Saloon\Http\SaloonConnector;
use Sammyjo20\Saloon\Traits\Plugins\AcceptsJson;
use Sammyjo20\Saloon\Traits\Plugins\HasJsonBody;
use Sammyjo20\Saloon\Traits\Plugins\WithDebugData;

class HeaderConnector extends SaloonConnector
{
    use AcceptsJson;
    use WithDebugData;
    use HasJsonBody;

    public function defineBaseUrl(): string
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
