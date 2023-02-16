<?php

declare(strict_types=1);

namespace Saloon\Repositories;

use InvalidArgumentException;
use Saloon\Traits\Conditionable;
use Saloon\Contracts\IntegerStore as IntegerStoreContract;

class IntegerStore implements IntegerStoreContract
{
    use Conditionable;

    /**
     * store Data
     *
     * @var int|null
     */
    protected ?int $data = null;

    /**
     * Constructor
     *
     * @param int|null $value
     */
    public function __construct(int|null $value = null)
    {
        $this->set($value);
    }

    /**
     * Set a value inside the store
     *
     * @param int|null $value
     * @return $this
     */
    public function set(mixed $value): static
    {
        if (! is_int($value)) {
            throw new InvalidArgumentException('The value must be an integer or null.');
        }

        $this->data = $value;

        return $this;
    }

    /**
     * Retrieve all in the store
     *
     * @return int|null
     */
    public function get(): ?int
    {
        return $this->data;
    }

    /**
     * Determine if the store is empty
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->data);
    }

    /**
     * Determine if the store is not empty
     *
     * @return bool
     */
    public function isNotEmpty(): bool
    {
        return ! $this->isEmpty();
    }
}
