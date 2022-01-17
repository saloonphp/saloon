<?php

namespace Sammyjo20\Saloon\Tests\Resources\Connectors;

use Sammyjo20\Saloon\Http\SaloonConnector;
use Sammyjo20\Saloon\Traits\Features\AcceptsJson;
use Sammyjo20\Saloon\Traits\Features\HasQueryParams;

class QueryParameterConnector extends SaloonConnector
{
    use AcceptsJson;
    use HasQueryParams;

    public function defineBaseUrl(): string
    {
        return apiUrl();
    }

    public function defaultQuery(): array
    {
        return [
            'sort' => 'first_name',
        ];
    }
}
