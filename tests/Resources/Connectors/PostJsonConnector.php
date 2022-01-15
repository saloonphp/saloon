<?php

namespace Sammyjo20\Saloon\Tests\Resources\Connectors;

use Sammyjo20\Saloon\Http\SaloonConnector;
use Sammyjo20\Saloon\Traits\Features\AcceptsJson;
use Sammyjo20\Saloon\Traits\Features\HasJsonBody;

class PostJsonConnector extends SaloonConnector
{
    use AcceptsJson;
    use HasJsonBody;

    public function defineBaseUrl(): string
    {
        return apiUrl();
    }

    public function defaultData(): array
    {
        return [
            'connectorId' => 1,
        ];
    }
}
