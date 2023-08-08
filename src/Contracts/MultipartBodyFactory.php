<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use Saloon\Data\MultipartValue;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\StreamFactoryInterface;

interface MultipartBodyFactory
{
    /**
     * Create a multipart body
     *
     * @param array<MultipartValue> $multipartValues
     */
    public function create(StreamFactoryInterface $streamFactory, array $multipartValues, string $boundary): StreamInterface;
}
