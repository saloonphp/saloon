<?php

declare(strict_types=1);

namespace Saloon\Contracts\Body;

interface ArrayBodyRepository extends BodyRepository
{
    /**
     * Constructor
     *
     * @param array $value
     */
    public function __construct(mixed $value);

    /**
     * Get a specific key of the array
     *
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Merge another array into the repository
     *
     * @param array $value
     * @return $this
     */
    public function merge(array $value): static;

    /**
     * Add an element to the repository.
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function add(string $key, mixed $value): static;
}
