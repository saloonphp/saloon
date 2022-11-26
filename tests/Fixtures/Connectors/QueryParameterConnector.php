<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

class QueryParameterConnector extends Connector
{
    use AcceptsJson;

    public function defineBaseUrl(): string
    {
        return apiUrl();
    }

    protected function defaultquery(): array
    {
        return [
            'sort' => 'first_name',
        ];
    }
}
