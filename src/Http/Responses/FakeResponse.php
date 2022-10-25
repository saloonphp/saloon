<?php

namespace Sammyjo20\Saloon\Http\Responses;

use Psr\Http\Message\StreamInterface;
use Sammyjo20\Saloon\Helpers\ContentBag;
use Sammyjo20\Saloon\Http\MockResponse;

/**
 * @property MockResponse $rawResponse
 */
class FakeResponse extends SaloonResponse
{
    public function body(): string
    {
        // TODO: Implement body() method.
    }

    public function stream(): StreamInterface
    {
        // TODO: Implement stream() method.
    }

    public function header(string $header): string
    {
        // TODO: Implement header() method.
    }

    public function headers(): ContentBag
    {
        // TODO: Implement headers() method.
    }

    public function status(): int
    {
        // TODO: Implement status() method.
    }

    public function close(): static
    {
        // TODO: Implement close() method.
    }

    public function toPsrResponse(): mixed
    {
        // TODO: Implement toPsrResponse() method.
    }
}
