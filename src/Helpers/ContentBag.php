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
        return $this->data[$key] ?? $default;
    }

    /**
     * Overwrite the entire ContentBag.
     *
     * @param array $data
     * @return $this
     */
    public function set(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Merge in data into the content bag.
     *
     * @param array $data
     * @return $this
     */
    public function merge(...$arrays): self
    {
        $this->data = array_merge($this->data, $arrays);

        return $this;
    }

    /**
     * Store and overwrite an item into the ContentBag.
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function put(string $key, mixed $value): self
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * Remove an item from the ContentBag.
     *
     * @param string $key
     * @return $this
     */
    public function delete(string $key): self
    {
        unset($this->data[$key]);

        return $this;
    }
}
