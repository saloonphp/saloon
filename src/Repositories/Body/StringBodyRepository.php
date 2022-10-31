<?php

namespace Sammyjo20\Saloon\Repositories\Body;

use Sammyjo20\Saloon\Contracts\Body\BodyRepository as BodyRepositoryContract;

class StringBodyRepository implements BodyRepositoryContract
{
    /**
     * Repository Data
     *
     * @var string|null
     */
    protected ?string $data = null;

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
     * Set a value inside the repository
     *
     * @param string|null $value
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
     * @return string|null
     */
    public function all(): ?string
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
     * Convert the repository into a string
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->all() ?? '';
    }
}
