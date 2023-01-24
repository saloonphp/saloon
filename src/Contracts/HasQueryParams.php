<?php

declare(strict_types=1);

namespace Saloon\Contracts;

interface HasQueryParams
{
    /**
     * Access the query parameters
     *
     * @return ArrayStore
     */
    public function query(): ArrayStore;
}
