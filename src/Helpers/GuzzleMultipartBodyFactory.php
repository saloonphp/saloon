<?php

namespace Saloon\Helpers;

use GuzzleHttp\Psr7\MultipartStream;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\StreamInterface;
use Saloon\Contracts\MultipartBodyFactory;
use Saloon\Data\MultipartValue;

class GuzzleMultipartBodyFactory implements MultipartBodyFactory
{
    /**
     * Create a multipart body
     *
     * @param StreamFactoryInterface $streamFactory
     * @param array<MultipartValue> $multipartValues
     * @param string $boundary
     * @return StreamInterface
     */
    public function create(StreamFactoryInterface $streamFactory, array $multipartValues, string $boundary): StreamInterface
    {
        $elements = array_map(static function (MultipartValue $value) {
            return array_filter([
                'name' => $value->name,
                'filename' => $value->filename,
                'contents' => $value->value,
                'headers' => $value->headers,
            ]);
        }, $multipartValues);

        return new MultipartStream($elements, $boundary);
    }
}
