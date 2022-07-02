<?php

namespace Sammyjo20\Saloon\Http;

use Sammyjo20\Saloon\Helpers\ContentBag;
use Sammyjo20\Saloon\Helpers\DataBag;
use Sammyjo20\Saloon\Traits\HasRequestProperties;
use Throwable;
use Psr\Http\Message\RequestInterface;
use Sammyjo20\Saloon\Data\MockExceptionClosure;

class MockResponse
{
    /**
     * Request Headers
     *
     * @var ContentBag
     */
    protected ContentBag $headers;

    /**
     * Request Data
     *
     * @var DataBag
     */
    protected DataBag $data;

    /**
     * Request Config
     *
     * @var ContentBag
     */
    protected ContentBag $config;

    /**
     * @var int
     */
    protected int $status;

    /**
     * @var MockExceptionClosure|null
     */
    protected ?MockExceptionClosure $exceptionClosure = null;

    /**
     * Create a new mock response
     *
     * @param int $status
     * @param array $data
     * @param array $headers
     * @param array $config
     */
    public function __construct(mixed $data = [], int $status = 200, array $headers = [], array $config = [])
    {
        $this->data = new DataBag($data);
        $this->status = $status;
        $this->headers = new ContentBag($headers);
        $this->config = new ContentBag($config);
    }

    /**
     * Create a new mock response
     *
     * @param mixed $data
     * @param int $status
     * @param array $headers
     * @param array $config
     * @return static
     */
    public static function make(mixed $data = [], int $status = 200, array $headers = [], array $config = []): self
    {
        return new static($data, $status, $headers, $config);
    }

    /**
     * Create a new mock response from a Saloon request.
     *
     * @param SaloonRequest $request
     * @param int $status
     * @return static
     */
    public static function fromRequest(SaloonRequest $request, int $status = 200): self
    {
        return new static($request->data()->all(), $status, $request->headers()->all(), $request->config()->all());
    }

    /**
     * Throw an exception on the request.
     *
     * @param callable|Throwable $value
     * @return $this
     */
    public function throw(callable|Throwable $value): self
    {
        $closure = $value instanceof Throwable ? static fn() => $value : $value;

        $this->exceptionClosure = new MockExceptionClosure($closure);

        return $this;
    }

    /**
     * Get the status from the responses
     *
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * Get the formatted data on the response.
     *
     * @return mixed
     * @throws \JsonException
     */
    public function getFormattedData(): mixed
    {
        return $this->data->isArray() ? json_encode($this->data->all(), JSON_THROW_ON_ERROR) : $this->data->all();
    }

    /**
     * Checks if the response throws an exception.
     *
     * @return bool
     */
    public function throwsException(): bool
    {
        return $this->exceptionClosure instanceof MockExceptionClosure;
    }

    /**
     * Retrieve the exception.
     *
     * @param RequestInterface $psrRequest
     * @return Throwable
     */
    public function getException(RequestInterface $psrRequest): Throwable
    {
        return $this->exceptionClosure->call($psrRequest);
    }

    /**
     * @return ContentBag
     */
    public function getHeaders(): ContentBag
    {
        return $this->headers;
    }

    /**
     * @return DataBag
     */
    public function getData(): DataBag
    {
        return $this->data;
    }

    /**
     * @return ContentBag
     */
    public function getConfig(): ContentBag
    {
        return $this->config;
    }
}
