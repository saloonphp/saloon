<?php

declare(strict_types=1);

namespace Saloon\Contracts;

interface ArrayStore
{
    /**
     * Retrieve all the items.
     *
     * @return array<string, mixed>
     */
    public function all(): array;

    /**
     * Retrieve a single item.
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Overwrite the entire repository's contents.
     *
     * @param array<string, mixed> $data
     * @return $this
     */
    public function set(array $data): static;

    /**
     * Merge in other arrays.
     *
     * @param array<string, mixed> ...$arrays
     * @return $this
     */
    public function merge(array ...$arrays): static;

    /**
     * Add an item to the repository.
     *
     * @return $this
     */
    public function add(string $key, mixed $value): static;

    /**
     * Remove an item from the store.
     *
     * @return $this
     */
    public function remove(string $key): static;

    /**
     * Determine if the store is empty
     */
    public function isEmpty(): bool;

    /**
     * Determine if the store is not empty
     */
    public function isNotEmpty(): bool;
}
