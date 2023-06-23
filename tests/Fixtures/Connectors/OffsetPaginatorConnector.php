<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\Connector;
use Saloon\Contracts\Request;
use Saloon\Contracts\HasPagination;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Http\Paginators\OffsetPaginator;

class OffsetPaginatorConnector extends Connector implements HasPagination
{
    use AcceptsJson;

    /**
     * Define the base url of the api.
     *
     * @return string
     */
    public function resolveBaseUrl(): string
    {
        return apiUrl();
    }

    /**
     * Create a paginator instance
     *
     * @param \Saloon\Contracts\Request $request
     * @param mixed ...$additionalArguments
     * @return \Saloon\Contracts\Paginator
     */
    public function paginate(Request $request, mixed ...$additionalArguments): OffsetPaginator
    {
        return new OffsetPaginator($this, $request, 5, ...$additionalArguments);
    }
}
