<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use Saloon\Repositories\Body\IntBodyRepository;

interface HasDelay
{
    /**
     * Access the delay
     *
     * @return IntBodyRepository
     */
    public function delay(): IntBodyRepository;
}
