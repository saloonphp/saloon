<?php

declare(strict_types=1);

namespace Saloon\Repositories\Body;

use Saloon\Exceptions\UnableToCastToStringException;
use Saloon\Traits\Conditionable;
use Saloon\Contracts\Body\BodyRepository;

class IntegerBodyRepository implements BodyRepository
{
    use Conditionable;

    /**
     * Repository Data
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
     * Set a value inside the repository
     *
     * @param int|null $value
     * @return $this
     */
    public function set(mixed $value): static
    {
        if(! is_int($value) && ! is_null($value))
            throw new \InvalidArgumentException('The value must be an integer or null.');

        $this->data = $value;

        return $this;
    }

    /**
     * Retrieve all in the repository
     *
     * @return int|null
     */
    public function all(): ?int
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

    public function greaterThan(int $value): bool
    {
        return $this->data > $value;
    }

    public function lesserThan(int $value): bool
    {
        return $this->data < $value;
    }

    public function greaterOrEqualThan(int $value): bool
    {
        return $this->data >= $value;
    }

    public function lesserOrEqualThan(int $value): bool
    {
        return $this->data <= $value;
    }

    public function equalTo(int $value): bool
    {
        return $this->data === $value;
    }

    /**
     * Convert the repository into a string
     *
     * @return string
     */
    public function __toString(): string
    {
        throw new UnableToCastToStringException('Casting the IntBodyRepository as a string is not supported.');
    }
}
