<?php

namespace Saloon\Traits\Body;

use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;

trait CreatesStreamFromString
{
    /**
     * Convert the body repository into a stream
     *
     * @param StreamFactoryInterface $streamFactory
     * @return StreamInterface
     */
    public function toStream(StreamFactoryInterface $streamFactory): StreamInterface
    {
        return $streamFactory->createStream((string)$this);
    }
}
