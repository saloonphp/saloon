<?php

declare(strict_types=1);

namespace Saloon\Traits\Connector;

use Saloon\Contracts\Request;
use Saloon\Contracts\RequestPaginator;

trait HasRequestPaginator
{
    abstract protected function resolvePaginator(Request $request, mixed ...$additionalArguments): RequestPaginator;

    public function paginate(Request $request, mixed ...$additionalArguments): RequestPaginator
    {
        return $this->resolvePaginator($request, ...$additionalArguments);
    }
}
