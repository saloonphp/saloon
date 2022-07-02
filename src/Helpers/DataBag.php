<?php

namespace Sammyjo20\Saloon\Helpers;

use Sammyjo20\Saloon\Data\DataBagType;
use Sammyjo20\Saloon\Data\RequestDataType;
use Sammyjo20\Saloon\Exceptions\DataBagException;

class DataBag
{
    /**
     * @var mixed
     */
    protected mixed $data = [];

    /**
     * @var DataBagType|null
     */
    protected ?DataBagType $type = null;

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
     * @throws DataBagException
     */
    public function set(mixed $data): self
    {
        $this->validateType($data);

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
     * Validate the data type.
     *
     * @param mixed $data
     * @return void
     * @throws DataBagException
     */
    protected function validateType(mixed $data): void
    {
        $type = $this->type;

        if (is_null($type)) {
            return;
        }

        if ($type->validateData($data) === false) {
            throw new DataBagException('Unacceptable data type. Allowed type: ' . $this->type->value);
        }
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
     * @return DataBagType|null
     */
    public function getType(): ?DataBagType
    {
        return $this->type;
    }

    /**
     * @param DataBagType|null $type
     * @return DataBag
     */
    public function setType(?DataBagType $type): self
    {
        $this->type = $type;

        return $this;
    }

    /**
     * @param RequestDataType $type
     * @return $this
     */
    public function setTypeFromRequestType(RequestDataType $type): self
    {
        $this->type = $type->isArrayable() ? DataBagType::ARRAY : DataBagType::MIXED;

        return $this;
    }
}
