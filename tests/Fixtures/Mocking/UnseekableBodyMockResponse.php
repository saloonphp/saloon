<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Mocking;

use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Saloon\Http\Faking\MockResponse;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

/**
 * A MockResponse that uses an unseekable body stream so we can test
 * that the response debugger buffers the body and shows it correctly.
 */
class UnseekableBodyMockResponse extends MockResponse
{
    public function createPsrResponse(ResponseFactoryInterface $responseFactory, StreamFactoryInterface $streamFactory): ResponseInterface
    {
        $response = parent::createPsrResponse($responseFactory, $streamFactory);
        $body = (string) $response->getBody();

        return $response->withBody(new UnseekableStream($body));
    }
}

/**
 * Stream that reports isSeekable() === false (e.g. like a network stream).
 */
final class UnseekableStream implements StreamInterface
{
    private string $contents;

    private int $position = 0;

    private bool $closed = false;

    public function __construct(string $contents)
    {
        $this->contents = $contents;
    }

    public function __toString(): string
    {
        return $this->contents;
    }

    public function close(): void
    {
        $this->closed = true;
    }

    public function detach()
    {
        $this->closed = true;

        return null;
    }

    public function getSize(): ?int
    {
        return strlen($this->contents);
    }

    public function tell(): int
    {
        return $this->position;
    }

    public function eof(): bool
    {
        return $this->position >= strlen($this->contents);
    }

    public function isSeekable(): bool
    {
        return false;
    }

    public function seek(int $offset, int $whence = SEEK_SET): void
    {
        throw new \RuntimeException('Stream is not seekable');
    }

    public function rewind(): void
    {
        throw new \RuntimeException('Stream is not seekable');
    }

    public function isWritable(): bool
    {
        return false;
    }

    public function write(string $string): int
    {
        throw new \RuntimeException('Stream is not writable');
    }

    public function isReadable(): bool
    {
        return ! $this->closed;
    }

    public function read(int $length): string
    {
        $chunk = substr($this->contents, $this->position, $length);
        $this->position += strlen($chunk);

        return $chunk;
    }

    public function getContents(): string
    {
        $remaining = substr($this->contents, $this->position);
        $this->position = strlen($this->contents);

        return $remaining;
    }

    public function getMetadata(?string $key = null)
    {
        return $key === null ? [] : null;
    }
}
