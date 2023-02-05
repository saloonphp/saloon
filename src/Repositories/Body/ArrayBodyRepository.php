<?php

declare(strict_types=1);

namespace Saloon\Repositories\Body;

use InvalidArgumentException;
use Saloon\Traits\Conditionable;
use Saloon\Contracts\Body\BodyRepository;
use Saloon\Exceptions\UnableToCastToStringException;

class ArrayBodyRepository implements BodyRepository
{
    use Conditionable;

    /**
     * Repository Data
     *
     * @var array<array-key, mixed>
     */
    protected array $data = [];

    /**
     * Constructor
     *
     * @param array<array-key, mixed> $value
     */
    public function __construct(array $value = [])
    {
        $this->set($value);
    }

    /**
     * Set a value inside the repository
     *
     * @param array<array-key, mixed> $value
     * @return $this
     */
    public function set(mixed $value): static
    {
        if (! is_array($value)) {
            throw new InvalidArgumentException('The value must be an array');
        }

        $this->data = $value;

        return $this;
    }

    /**
     * Merge another array into the repository
     *
     * @param array<array-key, mixed> ...$arrays
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
     * @param array-key|null $key
     * @param mixed|null $value
     * @return $this
     */
    public function add(string|int|null $key = null, mixed $value = null): static
    {
        isset($key)
            ? $this->data[$key] = $value
            : $this->data[] = $value;

        return $this;
    }

    /**
     * Get a specific key of the array
     *
     * @param array-key|null $key
     * @param mixed|null $default
     * @return ($key is null ? array<array-key, mixed> : mixed)
     */
    public function get(string|int|null $key = null, mixed $default = null): mixed
    {
        if (is_null($key)) {
            return $this->all();
        }

        return $this->data[$key] ?? $default;
    }

    /**
     * Remove an item from the repository.
     *
     * @param array-key $key
     * @return $this
     */
    public function remove(string|int $key): static
    {
        unset($this->data[$key]);

        return $this;
    }

    /**
     * Retrieve all in the repository
     *
     * @return array<array-key, mixed>
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * Determine if the repository is empty
     *
     * @return bool
     *
     * @phpstan-assert-if-false non-empty-array $this->data
     */
    public function isEmpty(): bool
    {
        return empty($this->data);
    }

    /**
     * Determine if the repository is not empty
     *
     * @return bool
     *
     * @phpstan-assert-if-true non-empty-array $this->data
     */
    public function isNotEmpty(): bool
    {
        return ! $this->isEmpty();
    }

    /**
     * Convert to a string
     *
     * @return string
     * @throws \Saloon\Exceptions\UnableToCastToStringException
     */
    public function __toString(): string
    {
        throw new UnableToCastToStringException('Casting the ArrayBodyRepository as a string is not supported.');
    }
}
