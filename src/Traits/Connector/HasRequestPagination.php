<?php

declare(strict_types=1);

namespace Saloon\Traits\Connector;

use Saloon\Contracts\Request;
use Saloon\Contracts\RequestPaginator;

trait HasRequestPagination
{
    /**
     * @param \Saloon\Contracts\Request $request
     * @param mixed ...$additionalArguments
     *
     * @return \Saloon\Contracts\RequestPaginator
     */
    abstract protected function resolvePaginator(Request $request, mixed ...$additionalArguments): RequestPaginator;

    /**
     * @param \Saloon\Contracts\Request $request
     * @param mixed ...$additionalArguments
     *
     * @return \Saloon\Contracts\RequestPaginator
     */
    public function paginate(Request $request, mixed ...$additionalArguments): RequestPaginator
    {
        return $this->resolvePaginator($request, ...$additionalArguments);
    }
}
