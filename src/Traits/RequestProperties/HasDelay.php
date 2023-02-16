<?php

declare(strict_types=1);

namespace Saloon\Traits\RequestProperties;

use Saloon\Repositories\IntegerStore;

trait HasDelay
{
    /**
     * Request Delay
     *
     * @var IntegerStore
     */
    protected IntegerStore $delay;

    /**
     * Delay repository
     *
     * @return \Saloon\Repositories\IntegerStore
     */
    public function delay(): IntegerStore
    {
        return $this->delay ??= new IntegerStore($this->defaultDelay());
    }

    /**
     * Default Delay
     *
     * @return ?int
     */
    protected function defaultDelay(): ?int
    {
        return null;
    }
}
