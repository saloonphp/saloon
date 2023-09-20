<?php

declare(strict_types=1);

namespace Saloon\Contracts\Body;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\StreamFactoryInterface;

interface BodyRepository
{
    /**
     * Set the raw data in the repository
     *
     * @return $this
     */
    public function set(mixed $value): static;

    /**
     * Retrieve the raw data in the repository.
     */
    public function all(): mixed;

    /**
     * Retrieve the raw data in the repository
     *
     * Alias of `all()`.
     */
    public function get(): mixed;

    /**
     * Determine if the repository is empty
     */
    public function isEmpty(): bool;

    /**
     * Determine if the repository is not empty
     */
    public function isNotEmpty(): bool;

    /**
     * Convert the body repository into a stream
     */
    public function toStream(StreamFactoryInterface $streamFactory): StreamInterface;
}
