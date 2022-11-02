<?php declare(strict_types=1);

namespace Sammyjo20\Saloon\Repositories;

use Sammyjo20\Saloon\Contracts\ArrayStore as ArrayStoreContract;

class ArrayStore implements ArrayStoreContract
{
    /**
     * The repository's store
     *
     * @var array
     */
    protected array $data = [];

    /**
     * Constructor
     *
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Retrieve all the items.
     *
     * @return array
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * Retrieve a single item.
     *
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     */
    public function get(string $key, mixed $default = null): mixed
    {
        return $this->all()[$key] ?? $default;
    }

    /**
     * Overwrite the entire repository.
     *
     * @param array $data
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
     * @param mixed ...$arrays
     * @return $this
     */
    public function merge(...$arrays): static
    {
        $this->data = array_merge($this->data, ...$arrays);

        return $this;
    }

    /**
     * Add an item to the repository.
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function add(string $key, mixed $value): static
    {
        $this->data[$key] = value($value);

        return $this;
    }

    /**
     * Add an item to the repository when a condition is true.
     *
     * @param bool $condition
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function addWhen(bool $condition, string $key, mixed $value): static
    {
        if ($condition === true) {
            return $this->add($key, $value);
        }

        return $this;
    }

    /**
     * Remove an item from the store.
     *
     * @param string $key
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
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->data);
    }

    /**
     * Determine if the store is not empty
     *
     * @return bool
     */
    public function isNotEmpty(): bool
    {
        return ! $this->isEmpty();
    }
}
