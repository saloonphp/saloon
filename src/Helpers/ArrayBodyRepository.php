<?php

namespace Sammyjo20\Saloon\Helpers;

use Sammyjo20\Saloon\Interfaces\Data\ArrayBodyRepository as ArrayBodyRepositoryContract;
use Sammyjo20\Saloon\Interfaces\Data\BodyRepository;

class ArrayBodyRepository implements ArrayBodyRepositoryContract
{
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
    public function __construct(mixed $value)
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
     * @param array $value
     * @return ArrayBodyRepositoryContract
     */
    public function merge(array $value): static
    {
        $this->data = array_merge($this->data, $value);

        return $this;
    }

    /**
     * Add an element to the repository.
     *
     * @param string $key
     * @param mixed $value
     * @return ArrayBodyRepositoryContract
     */
    public function add(string $key, mixed $value): static
    {
        $this->data[$key] = $value;

        return $this;
    }

    /**
     * Set a value inside the repository
     *
     * @param array $value
     * @return ArrayBodyRepositoryContract
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
}
