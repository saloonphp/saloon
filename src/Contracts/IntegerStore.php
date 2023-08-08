<?php

declare(strict_types=1);

namespace Saloon\Contracts;

interface IntegerStore
{
    /**
     * Set a value inside the repository
     *
     * @return $this
     */
    public function set(?int $value): static;

    /**
     * Retrieve all in the repository
     */
    public function get(): ?int;

    /**
     * Determine if the repository is empty
     */
    public function isEmpty(): bool;

    /**
     * Determine if the repository is not empty
     */
    public function isNotEmpty(): bool;
}
