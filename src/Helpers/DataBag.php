<?php

namespace Sammyjo20\Saloon\Helpers;

use Stringable;
use Sammyjo20\Saloon\Data\DataBagType;
use Sammyjo20\Saloon\Data\RequestDataType;
use Sammyjo20\Saloon\Interfaces\Arrayable;
use Sammyjo20\Saloon\Exceptions\DataBagException;

class DataBag implements Stringable
{
    /**
     * The Data
     *
     * @var mixed
     */
    protected string|array $data = [];

    /**
     * The Type Of Data
     *
     * @var DataBagType
     */
    protected DataBagType $type;

    /**
     * Constructor
     *
     * @param array|Arrayable|Stringable $data
     */
    public function __construct(array|Arrayable|Stringable $data = [])
    {
        $this->set($data);
    }

    /**
     * Retrieve all the data from the DataBag.
     *
     * @return string|array
     */
    public function all(): string|array
    {
        return $this->data;
    }

    /**
     * Retrieve a single item from the DataBag.
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
     * Overwrite the entire DataBag.
     *
     * @param array|string|Arrayable|Stringable $data
     * @return $this
     */
    public function set(array|string|Arrayable|Stringable $data): static
    {
        if ($data instanceof Arrayable) {
            $data = $data->toArray();
        }

        if ($data instanceof Stringable) {
            $data = $data->__toString();
        }

        $this->data = $data;
        $this->type = is_string($data) ? DataBagType::STRING : DataBagType::ARRAY;

        return $this;
    }

    /**
     * Merge in data into the DataBag.
     *
     * @param mixed ...$arrays
     * @return $this
     * @throws DataBagException
     */
    public function merge(...$arrays): static
    {
        if ($this->isNotArray()) {
            throw new DataBagException('This method is only available for array data.');
        }

        $this->data = array_merge($this->data, ...$arrays);

        return $this;
    }

    /**
     * Store and overwrite an item into the DataBag.
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     * @throws DataBagException
     */
    public function add(string $key, mixed $value): static
    {
        if ($this->isNotArray()) {
            throw new DataBagException('This method is only available for array data.');
        }

        $this->data[$key] = value($value);

        return $this;
    }

    /**
     * Remove an item from the DataBag.
     *
     * @param string $key
     * @return $this
     * @throws DataBagException
     */
    public function delete(string $key): static
    {
        if ($this->isNotArray()) {
            throw new DataBagException('This method is only available for array data.');
        }

        unset($this->data[$key]);

        return $this;
    }

    /**
     * Check if the data is an array.
     *
     * @return bool
     */
    public function isArray(): bool
    {
        return is_array($this->data);
    }

    /**
     * Check if the data is not an array.
     *
     * @return bool
     */
    public function isNotArray(): bool
    {
        return ! $this->isArray();
    }

    /**
     * Check if the data bag is empty.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->all());
    }

    /**
     * Check if the data bag is not empty.
     *
     * @return bool
     */
    public function isNotEmpty(): bool
    {
        return ! $this->isEmpty();
    }

    /**
     * Set the data type from a request's data type.
     *
     * @param RequestDataType $type
     * @return $this
     */
    public function setTypeFromRequestType(RequestDataType $type): static
    {
        $this->type = $type->isArrayable() ? DataBagType::ARRAY : DataBagType::STRING;

        return $this;
    }

    /**
     * Get the data type
     *
     * @return DataBagType|null
     */
    public function getType(): ?DataBagType
    {
        return $this->type;
    }

    /**
     * Convert to string
     *
     * @return string
     * @throws \JsonException
     */
    public function __toString(): string
    {
        $all = $this->all();

        return $this->isArray() ? json_encode($all, JSON_THROW_ON_ERROR) : $all;
    }
}
