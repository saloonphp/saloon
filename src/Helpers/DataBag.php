<?php

namespace Sammyjo20\Saloon\Helpers;

use Sammyjo20\Saloon\Exceptions\DataBagException;

class DataBag
{
    /**
     * @var mixed
     */
    protected mixed $data = [];

    /**
     * @param mixed $data
     */
    public function __construct(mixed $data = [])
    {
        $this->data = $data;
    }

    /**
     * Retrieve all the items from the ContentBag.
     *
     * @return array
     */
    public function all(): mixed
    {
        return $this->data;
    }

    /**
     * Retrieve a single item from the ContentBag.
     *
     * @param string $key
     * @param mixed|null $default
     * @return mixed
     * @throws DataBagException
     */
    public function get(string $key, mixed $default = null): mixed
    {
        if ($this->isNotArray()) {
            throw new DataBagException('This method is only available for array data.');
        }

        return $this->all()[$key] ?? $default;
    }

    /**
     * Overwrite the entire ContentBag. Will disable default values.
     *
     * @param mixed $data
     * @return $this
     */
    public function set(mixed $data): self
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Merge in data into the content bag.
     *
     * @param mixed ...$arrays
     * @return $this
     * @throws DataBagException
     */
    public function merge(...$arrays): self
    {
        if ($this->isNotArray()) {
            throw new DataBagException('This method is only available for array data.');
        }

        $this->data = array_merge($this->data, ...$arrays);

        return $this;
    }

    /**
     * Store and overwrite an item into the ContentBag.
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     * @throws DataBagException
     */
    public function push(string $key, mixed $value): self
    {
        if ($this->isNotArray()) {
            throw new DataBagException('This method is only available for array data.');
        }

        $this->data[$key] = $value;

        return $this;
    }

    /**
     * Remove an item from the ContentBag.
     *
     * @param string $key
     * @return $this
     * @throws DataBagException
     */
    public function delete(string $key): self
    {
        if ($this->isNotArray()) {
            throw new DataBagException('This method is only available for array data.');
        }

        unset($this->data[$key]);

        return $this;
    }

    /**
     * @return bool
     */
    public function isArray(): bool
    {
        return is_array($this->data);
    }

    /**
     * @return bool
     */
    public function isNotArray(): bool
    {
        return ! $this->isArray();
    }
}
