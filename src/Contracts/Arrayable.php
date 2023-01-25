<?php

declare(strict_types=1);

namespace Saloon\Contracts;

interface Arrayable
{
    /**
     * Convert the instance to an array
     *
     * @return array<array-key, mixed>
     */
    public function toArray(): array;
}
