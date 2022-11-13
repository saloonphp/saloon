<?php declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\SaloonConnector;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Traits\Plugins\HasJsonBody;
use Saloon\Traits\Plugins\WithDebugData;

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
