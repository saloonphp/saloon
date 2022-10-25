<?php

namespace Sammyjo20\Saloon\Http\Responses;

use Exception;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\StreamInterface;
use Sammyjo20\Saloon\Helpers\ContentBag;
use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\Saloon\Http\PendingSaloonRequest;

/**
 * @property MockResponse $rawResponse
 */
class FakeResponse extends SaloonResponse
{
    /**
     * Constructor
     *
     * @param PendingSaloonRequest $pendingSaloonRequest
     */
    public function __construct(PendingSaloonRequest $pendingSaloonRequest)
    {
        $rawResponse = $pendingSaloonRequest->getMockResponse();

        parent::__construct($pendingSaloonRequest, $rawResponse);

        $this->setMocked(true);
    }

    public function body(): string
    {
        return $this->rawResponse->getFormattedData();
    }

    public function stream(): StreamInterface
    {
        // TODO: Implement stream() method.
    }

    public function header(string $header): string
    {
        return $this->rawResponse->getHeaders()->get($header);
    }

    public function headers(): ContentBag
    {
        return $this->rawResponse->getHeaders();
    }

    public function status(): int
    {
        return $this->rawResponse->getStatus();
    }

    public function close(): static
    {
        // TODO: Implement close() method.
    }

    public function toPsrResponse(): mixed
    {
        return new Response($this->status(), $this->headers()->all(), $this->body());
    }
}
