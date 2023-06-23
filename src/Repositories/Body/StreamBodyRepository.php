<?php

declare(strict_types=1);

namespace Saloon\Repositories\Body;

use GuzzleHttp\Psr7\Utils;
use InvalidArgumentException;
use Saloon\Traits\Conditionable;
use Psr\Http\Message\StreamInterface;
use Saloon\Contracts\Body\BodyRepository;

class StreamBodyRepository implements BodyRepository
{
    use Conditionable;

    /**
     * The stream body
     *
     * @var StreamInterface|null
     */
    protected ?StreamInterface $stream = null;

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

        if (is_resource($value)) {
            $value = Utils::streamFor($value);
        }

        $this->stream = $value;

        return $this;
    }

    /**
     * Retrieve the stream from the repository
     *
     * @return StreamInterface|null
     */
    public function all(): ?StreamInterface
    {
        return $this->stream;
    }

    /**
     * Retrieve the stream from the repository
     *
     * Alias of "all" method.
     *
     * @return StreamInterface|null
     */
    public function get(): ?StreamInterface
    {
        return $this->all();
    }

    /**
     * Determine if the repository is empty
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return is_null($this->stream);
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
     * Get the contents of the stream as a string
     *
     * @return string
     */
    public function __toString(): string
    {
        $stream = &$this->stream;

        if (is_null($stream)) {
            return '';
        }

        $contents = $stream->getContents();

        if ($stream->isSeekable()) {
            $stream->rewind();
        }

        return $contents;
    }
}
