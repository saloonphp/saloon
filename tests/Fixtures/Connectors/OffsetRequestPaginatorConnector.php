<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Contracts\HasRequestPagination as HasRequestPaginationContract;
use Saloon\Contracts\Request;
use Saloon\Contracts\RequestPaginator;
use Saloon\Http\Connector;
use Saloon\Http\Paginators\OffsetRequestPaginator;
use Saloon\Traits\Connector\HasRequestPagination;
use Saloon\Traits\Plugins\AcceptsJson;

class OffsetRequestPaginatorConnector extends Connector implements HasRequestPaginationContract
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
        return new OffsetRequestPaginator($this, $request, ...$additionalArguments);
    }
}
