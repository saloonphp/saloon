<?php

declare(strict_types=1);

namespace Saloon\Repositories\Body;

use Saloon\Traits\Conditionable;
use Saloon\Contracts\Body\BodyRepository;

class StringBodyRepository implements BodyRepository
{
    use Conditionable;

    /**
     * Repository Data
     *
     * @var string|null
     */
    protected ?string $data = null;

    /**
     * Constructor
     *
     * @param string|null $value
     */
    public function __construct(string|null $value = null)
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
