<?php

declare(strict_types=1);

namespace Saloon\Contracts;

interface HasPagination
{
    /**
     * Create a paginator instance
     *
     * @param \Saloon\Contracts\Request $request
     * @param mixed ...$additionalArguments
     *
     * @return \Saloon\Contracts\Paginator
     */
    public function paginate(Request $request, mixed ...$additionalArguments): Paginator;
}
