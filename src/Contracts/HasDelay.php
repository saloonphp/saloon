<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use Saloon\Repositories\Body\IntegerBodyRepository;

interface HasDelay
{
    /**
     * Access the delay
     *
     * @return IntegerBodyRepository
     */
    public function delay(): IntegerBodyRepository;
}
