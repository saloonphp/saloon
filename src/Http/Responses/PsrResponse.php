<?php

namespace Sammyjo20\Saloon\Http\Responses;

use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\ResponseInterface;
use Sammyjo20\Saloon\Repositories\ArrayRepository;

/**
 * @property ResponseInterface $rawResponse
 */
class PsrResponse extends SaloonResponse
{
    /**
     * Get the body of the response as string.
     *
     * @return string
     */
    public function body(): string
    {
        return (string)$this->stream();
    }

    /**
     * Get the body as a stream. Don't forget to close the stream after using ->close().
     *
     * @return StreamInterface
     */
    public function stream(): StreamInterface
    {
        return $this->rawResponse->getBody();
    }

    /**
     * Get the headers from the response.
     *
     * @return ArrayRepository
     */
    public function headers(): ArrayRepository
    {
        return new ArrayRepository($this->rawResponse->getHeaders());
    }

    /**
     * Get the status code of the response.
     *
     * @return int
     */
    public function status(): int
    {
        return $this->rawResponse->getStatusCode();
    }

    /**
     * Get the underlying PSR response for the response.
     *
     * @return ResponseInterface
     */
    public function toPsrResponse(): ResponseInterface
    {
        return $this->rawResponse;
    }
}
