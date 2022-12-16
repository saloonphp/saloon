<?php

declare(strict_types=1);

namespace Saloon\Repositories\Body;

use Saloon\Exceptions\UnableToCastToStringException;
use Saloon\Contracts\Body\ArrayBodyRepository as ArrayBodyRepositoryContract;
use Saloon\Traits\Conditionable;

class ArrayBodyRepository implements ArrayBodyRepositoryContract
{
    use Conditionable;

    /**
     * Repository Data
     *
     * @var array
     */
    protected array $data = [];

    /**
     * Constructor
     *
     * @param array $value
     */
    public function __construct(mixed $value = [])
    {
        $this->set($value);
    }

    /**
     * Get a specific key of the array
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
     * Merge another array into the repository
     *
     * @param array ...$arrays
     * @return $this
     */
    public function merge(array ...$arrays): static
    {
        $this->data = array_merge($this->data, ...$arrays);

        return $this;
    }

    /**
     * Add an element to the repository.
     *
     * @param string $key
     * @param mixed $value
     * @return $this
     */
    public function add(string $key, mixed $value): static
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * Remove an item from the repository.
     *
     * @param string $key
     * @return self
     */
    public function remove(string $key): static
    {
        unset($this->data[$key]);

        return $this;
    }

    /**
     * Set a value inside the repository
     *
     * @param array $value
     * @return $this
     */
    public function set(mixed $value): static
    {
        $this->data = $value;

        return $this;
    }

    /**
     * Retrieve all in the repository
     *
     * @return array
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * Determine if the repository is empty
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->data);
    }

    /**
     * Determine if the repository is not empty
     *
     * @return bool
     */
    public function isNotEmpty(): bool
    {
        return ! $this->isEmpty();
    }

    /**
     * Convert to a string
     *
     * @return string
     * @throws UnableToCastToStringException
     */
    public function __toString(): string
    {
        throw new UnableToCastToStringException('Casting the ArrayBodyRepository as a string is not supported.');
    }
}
