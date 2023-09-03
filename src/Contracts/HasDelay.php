<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use Saloon\Repositories\IntegerStore;

/**
 * @internal
 */
interface HasDelay
{
    /**
     * Access the delay
     */
    public function delay(): IntegerStore;
}
