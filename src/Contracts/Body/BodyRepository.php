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
     * @param mixed $value
     * @return $this
     */
    public function set(mixed $value): static;

    /**
     * Retrieve the raw data in the repository
     *
     * @return mixed
     */
    public function get(): mixed;

    /**
     * Determine if the repository is empty
     *
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * Determine if the repository is not empty
     *
     * @return bool
     */
    public function isNotEmpty(): bool;

    /**
     * Convert the body repository into a stream
     *
     * @param StreamFactoryInterface $streamFactory
     * @return StreamInterface
     */
    public function toStream(StreamFactoryInterface $streamFactory): StreamInterface;
}
