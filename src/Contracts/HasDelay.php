<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use Saloon\Repositories\IntegerStore;

interface HasDelay
{
    /**
     * Access the delay
     *
     * @return IntegerStore
     */
    public function delay(): IntegerStore;
}
