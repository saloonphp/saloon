<?php declare(strict_types=1);

namespace Saloon\Contracts;

interface ArrayStore
{
    /**
     * Constructor
     *
     * @param array $data
     */
    public function __construct(array $data = []);

    /**
     * Retrieve all the items.
     *
     * @return array
     */
    public function all(): array;

    /**
     * Retrieve a single item.
     *
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed;

    /**
     * Overwrite the entire repository.
     *
     * @param array $data
     * @return \Sammyjo20\Saloon\Repositories\ArrayStore
     */
    public function set(array $data): static;

    /**
     * Merge in other arrays.
     *
     * @param mixed ...$arrays
     * @return \Sammyjo20\Saloon\Repositories\ArrayStore
     */
    public function merge(...$arrays): static;

    /**
     * Add an item to the repository.
     *
     * @param string $key
     * @param mixed $value
     * @return \Sammyjo20\Saloon\Repositories\ArrayStore
     */
    public function add(string $key, mixed $value): static;

    /**
     * Add an item to the repository when a condition is true.
     *
     * @param bool $condition
     * @param string $key
     * @param mixed $value
     * @return \Sammyjo20\Saloon\Repositories\ArrayStore
     */
    public function addWhen(bool $condition, string $key, mixed $value): static;

    /**
     * Remove an item from the store.
     *
     * @param string $key
     * @return \Sammyjo20\Saloon\Repositories\ArrayStore
     */
    public function remove(string $key): static;

    /**
     * Determine if the store is empty
     *
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * Determine if the store is not empty
     *
     * @return bool
     */
    public function isNotEmpty(): bool;
}
