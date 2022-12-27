<?php

declare(strict_types=1);

namespace Saloon\Contracts\Body;

use Stringable;

interface BodyRepository extends Stringable
{
    /**
     * Set a value inside the repository
     *
     * @param mixed $value
     * @return $this
     */
    public function set(mixed $value): static;

    /**
     * Retrieve all in the repository
     *
     * @return mixed
     */
    public function all(): mixed;

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
