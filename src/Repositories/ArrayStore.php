<?php

declare(strict_types=1);

namespace Saloon\Repositories;

use Saloon\Helpers\Helpers;
use Saloon\Traits\Conditionable;
use Saloon\Contracts\ArrayStore as ArrayStoreContract;

class ArrayStore implements ArrayStoreContract
{
    use Conditionable;

    /**
     * The repository's store
     *
     * @var array<string, mixed>
     */
    protected array $data = [];

    /**
     * Constructor
     *
     * @param array<string, mixed> $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Retrieve all the items.
     *
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * Retrieve a single item.
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    /**
     * Overwrite the entire repository.
     *
     * @param array<string, mixed> $data
     * @return $this
     */
    public function set(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Merge in other arrays.
     *
     * @param array<string, mixed> ...$arrays
     * @return $this
     */
    public function merge(array ...$arrays): static
    {
        $this->data = array_merge($this->data, ...$arrays);

        return $this;
    }

    /**
     * Add an item to the repository.
     *
     * @return $this
     */
    public function add(string $key, mixed $value): static
    {
        $this->data[$key] = Helpers::value($value);

        return $this;
    }

    /**
     * Remove an item from the store.
     *
     * @return $this
     */
    public function remove(string $key): static
    {
        unset($this->data[$key]);

        return $this;
    }

    /**
     * Determine if the store is empty
     *
     *
     * @phpstan-assert-if-false non-empty-array $this->data
     */
    public function isEmpty(): bool
    {
        return empty($this->data);
    }

    /**
     * Determine if the store is not empty
     *
     *
     * @phpstan-assert-if-true non-empty-array $this->data
     */
    public function isNotEmpty(): bool
    {
        return ! $this->isEmpty();
    }
}
