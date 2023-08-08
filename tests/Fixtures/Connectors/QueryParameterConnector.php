<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\Connector;
use Saloon\Traits\Plugins\AcceptsJson;

class QueryParameterConnector extends Connector
{
    use AcceptsJson;

    /**
     * Constructor
     */
    public function __construct(public ?string $url = null)
    {
        if (is_null($this->url)) {
            $this->url = apiUrl();
        }
    }

    public function resolveBaseUrl(): string
    {
        return $this->url;
    }

    protected function defaultQuery(): array
    {
        return [
            'sort' => 'first_name',
        ];
    }
}
