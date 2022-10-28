<?php

namespace Sammyjo20\Saloon\Http\Responses;

use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Stream;
use Psr\Http\Message\StreamInterface;
use Sammyjo20\Saloon\Helpers\ContentBag;
use Sammyjo20\Saloon\Http\SimulatedResponseData;

/**
 * @property SimulatedResponseData $rawResponse
 */
class SimulatedResponse extends SaloonResponse
{
    /**
     * Get the body of the response as string.
     *
     * @return string
     * @throws \JsonException
     */
    public function body(): string
    {
        return $this->rawResponse->getDataAsString();
    }

    /**
     * Get the body as a stream.
     *
     * @return StreamInterface
     * @throws \JsonException
     */
    public function stream(): StreamInterface
    {
        $stream = fopen('php://temp', 'rb+');

        fwrite($stream, $this->body());
        rewind($stream);

        return new Stream($stream);
    }

    /**
     * Get the headers from the response.
     *
     * @return ContentBag
     */
    public function headers(): ContentBag
    {
        return $this->rawResponse->getHeaders();
    }

    /**
     * Get the status code of the response.
     *
     * @return int
     */
    public function status(): int
    {
        return $this->rawResponse->getStatus();
    }

    /**
     * Get the underlying PSR response for the response.
     *
     * @return mixed
     * @throws \JsonException
     */
    public function toPsrResponse(): mixed
    {
        return new Response($this->status(), $this->headers()->all(), $this->body());
    }
}
