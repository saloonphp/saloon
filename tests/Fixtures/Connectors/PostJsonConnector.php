<?php

namespace Sammyjo20\Saloon\Tests\Fixtures\Connectors;

use Sammyjo20\Saloon\Http\SaloonConnector;
use Sammyjo20\Saloon\Interfaces\Data\WithBody;
use Sammyjo20\Saloon\Traits\Body\HasJsonBody;
use Sammyjo20\Saloon\Traits\Plugins\AcceptsJson;

class PostJsonConnector extends SaloonConnector implements WithBody
{
    use AcceptsJson;
    use HasJsonBody;

    public function defineBaseUrl(): string
    {
        return apiUrl();
    }

    public function defaultBody(): array
    {
        return [
            'connectorId' => 1,
        ];
    }
}
