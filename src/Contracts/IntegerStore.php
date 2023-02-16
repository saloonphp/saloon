<?php

namespace Saloon\Contracts;

interface IntegerStore
{
    /**
     * Set a value inside the repository
     *
     * @param int|null $value
     * @return \Saloon\Repositories\IntegerStore
     */
    public function set(mixed $value): static;

    /**
     * Retrieve all in the repository
     *
     * @return int|null
     */
    public function all(): ?int;

    /**
     * Determine if the repository is empty
     *
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * Determine if the repository is not empty
     *
     * @return bool
     */
    public function isNotEmpty(): bool;
}
