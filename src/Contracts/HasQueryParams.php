<?php

declare(strict_types=1);

namespace Saloon\Contracts;

/**
 * @internal
 */
interface HasQueryParams
{
    /**
     * Access the query parameters
     */
    public function query(): ArrayStore;
}
