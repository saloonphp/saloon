<?php

declare(strict_types=1);

namespace Saloon\Contracts;

/**
 * @internal
 */
interface HasHeaders
{
    /**
     * Access the headers
     */
    public function headers(): ArrayStore;
}
