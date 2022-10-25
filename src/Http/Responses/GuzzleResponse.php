<?php

namespace Sammyjo20\Saloon\Http\Responses;

use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\StreamInterface;
use Sammyjo20\Saloon\Helpers\ContentBag;

/**
 * @property Response $rawResponse
 */
class GuzzleResponse extends SaloonResponse
{
    /**
     * Get the body of the response as string.
     *
     * @return string
     */
    public function body(): string
    {
        return $this->rawResponse->getBody();
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
     * Get a header from the response.
     *
     * @param string $header
     * @return string
     */
    public function header(string $header): string
    {
        return $this->rawResponse->getHeaderLine($header);
    }

    /**
     * Get the headers from the response.
     *
     * @return array
     */
    public function headers(): ContentBag
    {
        $headers = $this->rawResponse->getHeaders();

        // Todo: Convert them into header dtos? (Would standardise headers with multiple values)

        dd($headers);
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
     * Close the stream and any underlying resources.
     *
     * @return $this
     */
    public function close(): static
    {
        $this->rawResponse->getBody()->close();

        return $this;
    }

    /**
     * Get the underlying PSR response for the response.
     *
     * @return Response
     */
    public function toPsrResponse(): Response
    {
        return $this->rawResponse;
    }
}
