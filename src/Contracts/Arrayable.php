<?php

declare(strict_types=1);

namespace Saloon\Contracts;

interface Arrayable
{
    /**
     * Convert the instance to an array
     *
     * @return array
     */
    public function toArray(): array;
}
