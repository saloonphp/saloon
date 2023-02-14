<?php

declare(strict_types=1);

namespace Saloon\Traits\RequestProperties;

use Ramsey\Uuid\Type\Integer;
use Saloon\Repositories\Body\IntegerBodyRepository;

trait HasDelay
{
    /**
     * Request Delay
     *
     * @var IntegerBodyRepository
     */
    protected IntegerBodyRepository $delay;

    /**
     * Delay repository
     *
     * @return ?IntegerBodyRepository
     */
    public function delay(): IntegerBodyRepository
    {
        return $this->delay ??= new IntegerBodyRepository($this->defaultDelay());
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
