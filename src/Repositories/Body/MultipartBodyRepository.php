<?php

declare(strict_types=1);

namespace Saloon\Repositories\Body;

use Saloon\Helpers\Arr;
use InvalidArgumentException;
use Saloon\Contracts\Arrayable;
use Saloon\Data\MultipartValue;
use Saloon\Traits\Conditionable;
use Saloon\Contracts\Body\BodyRepository;
use Saloon\Exceptions\UnableToCastToStringException;

class MultipartBodyRepository implements BodyRepository, Arrayable
{
    use Conditionable;

    /**
     * Base Repository
     *
     * @var \Saloon\Repositories\Body\ArrayBodyRepository
     */
    protected ArrayBodyRepository $data;

    /**
     * Constructor
     *
     * @param array<\Saloon\Data\MultipartValue> $value
     */
    public function __construct(array $value = [])
    {
        $this->data = new ArrayBodyRepository;

        $this->set($value);
    }

    /**
     * Set a value inside the repository
     *
     * @param array<\Saloon\Data\MultipartValue> $value
     * @return $this
     */
    public function set(mixed $value): static
    {
        if (! is_array($value)) {
            throw new InvalidArgumentException('The value must be an array');
        }

        $this->data->set(
            $this->parseMultipartArray($value)
        );

        return $this;
    }

    /**
     * Merge another array into the repository
     *
     * @param array<\Saloon\Data\MultipartValue> ...$arrays
     * @return $this
     */
    public function merge(array ...$arrays): static
    {
        $this->data->merge(...array_map(
            $this->parseMultipartArray(...),
            $arrays,
        ));

        return $this;
    }

    /**
     * Add an element to the repository.
     *
     * @param string $name
     * @param \Psr\Http\Message\StreamInterface|resource|string $contents
     * @param string|null $filename
     * @param array<string, mixed> $headers
     * @return $this
     */
    public function add(string $name, mixed $contents, string $filename = null, array $headers = []): static
    {
        $this->attach(new MultipartValue($name, $contents, $filename, $headers));

        return $this;
    }

    /**
     * Attach a multipart file
     *
     * @param \Saloon\Data\MultipartValue $file
     * @return $this
     */
    public function attach(MultipartValue $file): static
    {
        $this->data->add($file->name, $file);

        return $this;
    }

    /**
     * Get a specific key of the array
     *
     * @param array-key $key
     * @param mixed|null $default
     * @return \Saloon\Data\MultipartValue
     */
    public function get(string|int $key, mixed $default = null): MultipartValue
    {
        return $this->data->get($key, $default);
    }

    /**
     * Remove an item from the repository.
     *
     * @param string $key
     * @return $this
     */
    public function remove(string $key): static
    {
        $this->data->remove($key);

        return $this;
    }

    /**
     * Retrieve all in the repository
     *
     * @return array<\Saloon\Data\MultipartValue>
     */
    public function all(): array
    {
        return $this->data->all();
    }

    /**
     * Determine if the repository is empty
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return $this->data->isEmpty();
    }

    /**
     * Determine if the repository is not empty
     *
     * @return bool
     */
    public function isNotEmpty(): bool
    {
        return $this->data->isNotEmpty();
    }

    /**
     * Convert to string
     *
     * @return string
     * @throws \Saloon\Exceptions\UnableToCastToStringException
     */
    public function __toString(): string
    {
        throw new UnableToCastToStringException('Casting the MultipartBodyRepository as a string is not supported.');
    }

    /**
     * Convert the instance to an array
     *
     * Converts all the multipart value objects into arrays.
     *
     * @return array<array{
     *     name: string,
     *     contents: mixed,
     *     filename: string|null,
     *     headers: array<string, mixed>,
     * }>
     */
    public function toArray(): array
    {
        return array_values(array_map(static fn (MultipartValue $value) => $value->toArray(), $this->all()));
    }

    /**
     * Parse a multipart array
     *
     * @param array<string, mixed> $value
     * @return array<\Saloon\Data\MultipartValue>
     */
    protected function parseMultipartArray(array $value): array
    {
        $multipartValues = array_filter($value, static fn (mixed $item): bool => $item instanceof MultipartValue);

        if (count($value) !== count($multipartValues)) {
            throw new InvalidArgumentException(sprintf('The value array must only contain %s objects.', MultipartValue::class));
        }

        return Arr::mapWithKeys($multipartValues, static fn (MultipartValue $value) => [$value->name => $value]);
    }
}
