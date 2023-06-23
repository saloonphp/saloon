<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\Connector;
use Saloon\Contracts\Request;
use Saloon\Contracts\HasPagination;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Http\Paginators\PagedPaginator;

class PagePaginatorConnector extends Connector implements HasPagination
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
     * @return \Saloon\Http\Paginators\PagedPaginator
     */
    public function paginate(Request $request, mixed ...$additionalArguments): PagedPaginator
    {
        return new PagedPaginator($this, $request, 5, ...$additionalArguments);
    }
}
