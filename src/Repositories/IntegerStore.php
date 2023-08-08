<?php

declare(strict_types=1);

namespace Saloon\Repositories;

use Saloon\Traits\Conditionable;
use Saloon\Contracts\IntegerStore as IntegerStoreContract;

class IntegerStore implements IntegerStoreContract
{
    use Conditionable;

    /**
     * store Data
     */
    protected ?int $data = null;

    /**
     * Constructor
     */
    public function __construct(?int $value = null)
    {
        $this->set($value);
    }

    /**
     * Set a value inside the store
     *
     * @return $this
     */
    public function set(?int $value): static
    {
        $this->data = $value;

        return $this;
    }

    /**
     * Retrieve all in the store
     */
    public function get(): ?int
    {
        return $this->data;
    }

    /**
     * Determine if the store is empty
     */
    public function isEmpty(): bool
    {
        return empty($this->data);
    }

    /**
     * Determine if the store is not empty
     */
    public function isNotEmpty(): bool
    {
        return ! $this->isEmpty();
    }
}
