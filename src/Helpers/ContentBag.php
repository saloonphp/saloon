<?php

namespace Sammyjo20\Saloon\Helpers;

class ContentBag
{
    /**
     * @var array
     */
    protected array $data = [];

    /**
     * @param array $data
     */
    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * Retrieve all the items from the ContentBag.
     *
     * @return array
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * Retrieve a single item from the ContentBag.
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
     * Overwrite the entire ContentBag. Will disable default values.
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
     * Merge in data into the content bag.
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
     * Store and overwrite an item into the ContentBag.
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
     * Store a given value if a given condition is true.
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
     * Remove an item from the ContentBag.
     *
     * @param string $key
     * @return $this
     */
    public function remove(string $key): static
    {
        unset($this->data[$key]);

        return $this;
    }
}
