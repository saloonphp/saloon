<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Contracts\HasRequestPagination as HasRequestPaginationContract;
use Saloon\Contracts\Request;
use Saloon\Contracts\RequestPaginator;
use Saloon\Http\Connector;
use Saloon\Http\Paginators\PageRequestPaginator;
use Saloon\Traits\Connector\HasRequestPagination;
use Saloon\Traits\Plugins\AcceptsJson;

class PageRequestPaginatorConnector extends Connector implements HasRequestPaginationContract
{
    use AcceptsJson;
    use HasRequestPagination;

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
     * @param \Saloon\Contracts\Request $request
     * @param mixed ...$additionalArguments
     *
     * @return \Saloon\Contracts\RequestPaginator
     */
    protected function resolvePaginator(Request $request, mixed ...$additionalArguments): RequestPaginator
    {
        return new PageRequestPaginator($this, $request, ...$additionalArguments);
    }
}
