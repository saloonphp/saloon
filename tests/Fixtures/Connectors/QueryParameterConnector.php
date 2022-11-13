<?php declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\SaloonConnector;
use Saloon\Traits\Plugins\AcceptsJson;

class QueryParameterConnector extends SaloonConnector
{
    use AcceptsJson;

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
