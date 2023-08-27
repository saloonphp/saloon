<?php

declare(strict_types=1);

namespace Saloon\Http;

use Saloon\Contracts\Connector;
use Throwable;
use Saloon\Contracts\Request;
use Saloon\Repositories\ArrayStore;
use Saloon\Contracts\PendingRequest;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Saloon\Traits\Responses\HasResponseHelpers;
use Saloon\Contracts\Response as ResponseContract;
use Saloon\Contracts\ArrayStore as ArrayStoreContract;

class Response implements ResponseContract
{
    use HasResponseHelpers;

    /**
     * The PSR request
     */
    protected RequestInterface $psrRequest;

    /**
     * The PSR response from the sender.
     */
    protected ResponseInterface $psrResponse;

    /**
     * The pending request that has all the request properties
     */
    protected PendingRequest $pendingRequest;

    /**
     * The original sender exception
     */
    protected ?Throwable $senderException = null;

    /**
     * Create a new response instance.
     */
    public function __construct(ResponseInterface $psrResponse, PendingRequest $pendingRequest, RequestInterface $psrRequest, Throwable $senderException = null)
    {
        $this->psrRequest = $psrRequest;
        $this->psrResponse = $psrResponse;
        $this->pendingRequest = $pendingRequest;
        $this->senderException = $senderException;
    }

    /**
     * Create a new response instance
     */
    public static function fromPsrResponse(ResponseInterface $psrResponse, PendingRequest $pendingRequest, RequestInterface $psrRequest, ?Throwable $senderException = null): static
    {
        return new static($psrResponse, $pendingRequest, $psrRequest, $senderException);
    }

    /**
     * Get the pending request that created the response.
     */
    public function getPendingRequest(): PendingRequest
    {
        return $this->pendingRequest;
    }

    /**
     * Get the connector that sent the request
     */
    public function getConnector(): Connector
    {
        return $this->pendingRequest->getConnector();
    }

    /**
     * Get the original request that created the response.
     *
     * @deprecated Will be removed in Saloon v4. Use $response->getPendingRequest()->getRequest() instead.
     */
    public function getRequest(): Request
    {
        return $this->pendingRequest->getRequest();
    }

    /**
     * Get the PSR-7 request
     */
    public function getPsrRequest(): RequestInterface
    {
        return $this->psrRequest;
    }

    /**
     * Create a PSR response from the raw response.
     */
    public function getPsrResponse(): ResponseInterface
    {
        return $this->psrResponse;
    }

    /**
     * Get the body of the response as string.
     */
    public function body(): string
    {
        return $this->stream()->getContents();
    }

    /**
     * Get the body as a stream. Don't forget to close the stream after using ->close().
     */
    public function stream(): StreamInterface
    {
        $stream = $this->psrResponse->getBody();

        if ($stream->isSeekable()) {
            $stream->rewind();
        }

        return $stream;
    }

    /**
     * Get the headers from the response.
     */
    public function headers(): ArrayStoreContract
    {
        $headers = array_map(static function (array $header) {
            return count($header) === 1 ? $header[0] : $header;
        }, $this->psrResponse->getHeaders());

        return new ArrayStore($headers);
    }

    /**
     * Get the status code of the response.
     */
    public function status(): int
    {
        return $this->psrResponse->getStatusCode();
    }

    /**
     * Get the original sender exception
     */
    public function getSenderException(): ?Throwable
    {
        return $this->senderException;
    }
}
