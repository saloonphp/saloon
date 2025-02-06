<?php

declare(strict_types=1);

namespace Saloon\Repositories\Body;

use LogicException;
use InvalidArgumentException;
use Saloon\Traits\Conditionable;
use Psr\Http\Message\StreamInterface;
use Saloon\Contracts\Body\MergeableBody;
use Saloon\Contracts\Body\BodyRepository;
use Psr\Http\Message\StreamFactoryInterface;

class ArrayBodyRepository implements BodyRepository, MergeableBody
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
     * Recursively merge another array into the repository
     *
     * @param array<array-key, mixed> ...$arrays
     * @return $this
     */
    public function merge(array ...$arrays): static
    {
        foreach ($arrays as $array) {
            $this->data = $this->safeRecursiveMerge($this->data, $array);
        }

        return $this;
    }

    /**
     * Safely merge arrays recursively while handling flat arrays properly.
     *
     * @param array<array-key, mixed> $array1 The original array.
     * @param array<array-key, mixed> $array2 The array being merged into the original array.
     * @return array<array-key, mixed> A properly merged array.
     */
    private function safeRecursiveMerge(array $array1, array $array2): array
    {
        foreach ($array2 as $key => $value) {
            // Handle if both are arrays (flat or nested)
            if (isset($array1[$key]) && is_array($array1[$key]) && is_array($value)) {
                // Check if arrays are flat (associative vs. sequential arrays)
                if ($this->isSequentialArray($array1[$key]) && $this->isSequentialArray($value)) {
                    // Concatenate flat arrays
                    $array1[$key] = array_merge($array1[$key], $value);
                } else {
                    // Recursively merge nested arrays
                    $array1[$key] = $this->safeRecursiveMerge($array1[$key], $value);
                }
            } else {
                // Overwrite non-array values or add missing keys
                $array1[$key] = $value;
            }
        }

        return $array1;
    }

    /**
     * Determine if an array is sequential (flat).
     *
     * Sequential arrays have keys as consecutive integers starting from 0.
     *
     * @param array<array-key, mixed> $array The array to check.
     * @return bool True if the array is sequential, otherwise false.
     */
    private function isSequentialArray(array $array): bool
    {
        return array_keys($array) === range(0, count($array) - 1);
    }

    /**
     * Add an element to the repository.
     *
     * @param array-key|null $key
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
     * Get the raw data in the repository.
     *
     * @return array<mixed, mixed>
     */
    public function all(): array
    {
        return $this->data;
    }

    /**
     * Get a specific key of the array
     *
     * Alias of `all()`.
     *
     * @param array-key|null $key
     * @return ($key is null ? array<array-key, mixed> : mixed)
     */
    public function get(string|int|null $key = null, mixed $default = null): mixed
    {
        if (is_null($key)) {
            return $this->all();
        }

        return $this->all()[$key] ?? $default;
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
     * Determine if the repository is empty
     *
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
     *
     * @phpstan-assert-if-true non-empty-array $this->data
     */
    public function isNotEmpty(): bool
    {
        return ! $this->isEmpty();
    }

    /**
     * Convert the body repository into a stream
     */
    public function toStream(StreamFactoryInterface $streamFactory): StreamInterface
    {
        throw new LogicException('Unable to create a stream directly from an array body repository.');
    }
}
