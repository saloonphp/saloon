<?php

declare(strict_types=1);

namespace Saloon\Repositories\Body;

use Saloon\Contracts\Body\MergeableBody;
use Saloon\Helpers\Arr;
use Saloon\Helpers\Str;
use InvalidArgumentException;
use Saloon\Data\MultipartValue;
use Saloon\Traits\Conditionable;
use Saloon\Exceptions\BodyException;
use Psr\Http\Message\StreamInterface;
use Saloon\Contracts\Body\BodyRepository;
use Saloon\Contracts\MultipartBodyFactory;
use Psr\Http\Message\StreamFactoryInterface;
use Saloon\Exceptions\UnableToCastToStringException;

class MultipartBodyRepository implements BodyRepository, MergeableBody
{
    use Conditionable;

    /**
     * Base Repository
     *
     * @var \Saloon\Repositories\Body\ArrayBodyRepository
     */
    protected ArrayBodyRepository $data;

    /**
     * The Multipart Boundary
     *
     * @var string
     */
    protected string $boundary;

    /**
     * Multipart Body Factory
     *
     * @var MultipartBodyFactory
     */
    protected MultipartBodyFactory $multipartBodyFactory;

    /**
     * Constructor
     *
     * @param array<\Saloon\Data\MultipartValue> $value
     * @throws \Exception
     */
    public function __construct(array $value = [], string $boundary = null)
    {
        $this->data = new ArrayBodyRepository;
        $this->boundary = is_null($boundary) ? Str::random(40) : $boundary;

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

    /**
     * Set the multipart body factory
     *
     * @param MultipartBodyFactory $multipartBodyFactory
     * @return MultipartBodyRepository
     */
    public function setMultipartBodyFactory(MultipartBodyFactory $multipartBodyFactory): MultipartBodyRepository
    {
        $this->multipartBodyFactory = $multipartBodyFactory;

        return $this;
    }

    /**
     * Get the boundary
     *
     * @return string
     */
    public function getBoundary(): string
    {
        return $this->boundary;
    }

    /**
     * Convert the body repository into a stream
     *
     * @param StreamFactoryInterface $streamFactory
     * @return StreamInterface
     * @throws BodyException
     */
    public function toStream(StreamFactoryInterface $streamFactory): StreamInterface
    {
        if (! isset($this->multipartBodyFactory)) {
            throw new BodyException('Unable to create a multipart body stream because the multipart body factory was not set.');
        }

        return $this->multipartBodyFactory->create($streamFactory, $this->all(), $this->getBoundary());
    }
}
