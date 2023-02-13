<?php

declare(strict_types=1);

namespace Saloon\Traits\RequestProperties;

use Saloon\Repositories\Body\IntBodyRepository;

trait HasDelay
{
    /**
     * Request Delay
     *
     * @var IntBodyRepository
     */
    protected IntBodyRepository $delay;

    public function delay(): IntBodyRepository
    {
        return $this->delay ??= new IntBodyRepository($this->defaultDelay());
    }

    /**
     * Default Delay
     *
     * @return int
     */
    protected function defaultDelay(): int
    {
        return 0;
    }
}
