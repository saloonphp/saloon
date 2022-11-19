<?php declare(strict_types=1);

namespace Saloon\Http\Responses;

use Throwable;
use Saloon\Traits\Macroable;
use Saloon\Contracts\Request;
use Saloon\Repositories\ArrayStore;
use Saloon\Contracts\PendingRequest;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\ResponseInterface;
use Saloon\Traits\Responses\HasResponseHelpers;
use Saloon\Http\Faking\SimulatedResponsePayload;
use Saloon\Contracts\Response as ResponseContract;

class Response implements ResponseContract
{
    use Macroable;
    use HasResponseHelpers;

    /**
     * The request options we attached to the request.
     *
     * @var PendingRequest
     */
    protected PendingRequest $pendingRequest;

    /**
     * The raw PSR response from the sender.
     *
     * @var ResponseInterface|mixed
     */
    protected ResponseInterface $rawResponse;

    /**
     * The original request exception
     *
     * @var Throwable|null
     */
    protected ?Throwable $requestException = null;

    /**
     * Create a new response instance.
     *
     * @param PendingRequest $pendingRequest
     * @param ResponseInterface|SimulatedResponsePayload $rawResponse
     * @param Throwable|null $requestException
     */
    public function __construct(PendingRequest $pendingRequest, ResponseInterface|SimulatedResponsePayload $rawResponse, Throwable $requestException = null)
    {
        if ($rawResponse instanceof SimulatedResponsePayload) {
            $rawResponse = $rawResponse->getPsrResponse();
        }

        $this->pendingRequest = $pendingRequest;
        $this->rawResponse = $rawResponse;
        $this->requestException = $requestException;
    }

    /**
     * Get the pending request that created the response.
     *
     * @return PendingRequest
     */
    public function getPendingRequest(): PendingRequest
    {
        return $this->pendingRequest;
    }

    /**
     * Get the original request that created the response.
     *
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->pendingRequest->getRequest();
    }

    /**
     * Get the request exception
     *
     * @return Throwable|null
     */
    public function getRequestException(): ?Throwable
    {
        return $this->requestException;
    }

    /**
     * Get the raw response
     *
     * @return mixed
     */
    public function getRawResponse(): mixed
    {
        return $this->rawResponse;
    }

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
     * @return ArrayStore
     */
    public function headers(): ArrayStore
    {
        return new ArrayStore($this->rawResponse->getHeaders());
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
     * Create a PSR response from the raw response.
     *
     * @return ResponseInterface
     */
    public function getPsrResponse(): ResponseInterface
    {
        return $this->rawResponse;
    }
}
