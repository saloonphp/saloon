<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use Throwable;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\ResponseInterface;

interface HttpResponse extends Response
{
    /**
     * Create an instance of the response from a PSR response
     *
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     * @param \Psr\Http\Message\ResponseInterface $psrResponse
     * @param \Throwable|null $senderException
     * @return $this
     */
    public static function fromPsrResponse(ResponseInterface $psrResponse, PendingRequest $pendingRequest, ?Throwable $senderException = null): static;

    /**
     * Create a PSR response from the raw response.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function getPsrResponse(): ResponseInterface;

    /**
     * Get the body as a stream. Don't forget to close the stream after using ->close().
     *
     * @return \Psr\Http\Message\StreamInterface
     */
    public function stream(): StreamInterface;

    /**
     * Close the stream and any underlying resources.
     *
     * @return $this
     */
    public function close(): static;
}
