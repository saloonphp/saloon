<?php

namespace Saloon\Contracts;

use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Saloon\Data\MultipartValue;

interface MultipartBodyFactory
{
    /**
     * Create a multipart body
     *
     * @param StreamFactoryInterface $streamFactory
     * @param array<MultipartValue> $multipartValues
     * @param string $boundary
     * @return StreamInterface
     */
    public function create(StreamFactoryInterface $streamFactory, array $multipartValues, string $boundary): StreamInterface;
}
