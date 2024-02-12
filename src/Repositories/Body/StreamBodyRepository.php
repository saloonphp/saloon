<?php

declare(strict_types=1);

namespace Saloon\Repositories\Body;

use InvalidArgumentException;
use Saloon\Traits\Conditionable;
use Psr\Http\Message\StreamInterface;
use Saloon\Contracts\Body\BodyRepository;
use Psr\Http\Message\StreamFactoryInterface;

class StreamBodyRepository implements BodyRepository
{
    use Conditionable;

    /**
     * The stream body
     *
     * @var StreamInterface|resource|null
     */
    protected mixed $stream = null;

    /**
     * Constructor
     *
     * @param StreamInterface|resource|null $value
     */
    public function __construct(mixed $value = null)
    {
        $this->set($value);
    }

    /**
     * Set a value inside the repository
     *
     * @param StreamInterface|resource|null $value
     * @return $this
     */
    public function set(mixed $value): static
    {
        if (isset($value) && ! $value instanceof StreamInterface && ! is_resource($value)) {
            throw new InvalidArgumentException('The value must a resource or be an instance of ' . StreamInterface::class);
        }

        $this->stream = $value;

        return $this;
    }

    /**
     * Retrieve the stream from the repository
     */
    public function all(): mixed
    {
        return $this->stream;
    }

    /**
     * Retrieve the stream from the repository
     *
     * Alias of "all" method.
     */
    public function get(): mixed
    {
        return $this->all();
    }

    /**
     * Determine if the repository is empty
     */
    public function isEmpty(): bool
    {
        return is_null($this->stream);
    }

    /**
     * Determine if the repository is not empty
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
        $stream = $this->stream;

        return $stream instanceof StreamInterface ? $stream : $streamFactory->createStreamFromResource($stream);
    }
}
