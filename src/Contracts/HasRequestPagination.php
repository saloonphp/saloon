<?php

declare(strict_types=1);

namespace Saloon\Contracts;

interface HasRequestPagination
{
    /**
     * @param \Saloon\Contracts\Request $request
     * @param mixed ...$additionalArguments
     *
     * @return \Saloon\Contracts\RequestPaginator
     */
    public function paginate(Request $request, mixed ...$additionalArguments): RequestPaginator;
}
