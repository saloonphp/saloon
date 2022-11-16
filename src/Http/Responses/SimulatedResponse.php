<?php declare(strict_types=1);

namespace Saloon\Http\Responses;

use GuzzleHttp\Psr7\Stream;
use Saloon\Http\Faking\MockResponse;
use Saloon\Repositories\ArrayStore;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use Saloon\Http\Faking\SimulatedResponsePayload;

class SimulatedResponse extends Response
{
    /**
     * The raw response from the sender.
     *
     * @var SimulatedResponsePayload
     */
    protected SimulatedResponsePayload $rawResponse;

    /**
     * Get the body of the response as string.
     *
     * @return string
     * @throws \JsonException
     */
    public function body(): string
    {
        return $this->rawResponse->getBodyAsString();
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
     * @return ArrayStore
     */
    public function headers(): ArrayStore
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
    public function getPsrResponse(): ResponseInterface
    {
        return new GuzzleResponse($this->status(), $this->headers()->all(), $this->body());
    }

    /**
     * Determine if the response has been mocked.
     *
     * @return bool
     */
    public function isMocked(): bool
    {
        return $this->rawResponse instanceof MockResponse;
    }
}
