<?php

namespace Sammyjo20\Saloon\Http;

use Psr\Http\Message\RequestInterface;
use Sammyjo20\Saloon\Data\MockExceptionClosure;
use Sammyjo20\Saloon\Exceptions\DirectoryNotFoundException;
use Sammyjo20\Saloon\Helpers\ContentBag;
use Sammyjo20\Saloon\Repositories\BodyRepository;
use Throwable;

class SimulatedResponseData
{
    /**
     * HTTP Status Code
     *
     * @var int
     */
    protected int $status;

    /**
     * Headers
     *
     * @var ContentBag
     */
    protected ContentBag $headers;

    /**
     * Request Body
     *
     * @var BodyRepository
     */
    protected BodyRepository $data;

    /**
     * Exception Closure
     *
     * @var MockExceptionClosure|null
     */
    protected ?MockExceptionClosure $exceptionClosure = null;

    /**
     * Create a new mock response
     *
     * @param int $status
     * @param array $data
     * @param array $headers
     */
    public function __construct(int $status = 200, mixed $data = [], array $headers = [])
    {
        $this->status = $status;
        $this->data = new BodyRepository($data);
        $this->headers = new ContentBag($headers);
    }

    /**
     * Create a new mock response
     *
     * @param mixed $data
     * @param int $status
     * @param array $headers
     * @return static
     */
    public static function make(int $status = 200, mixed $data = [], array $headers = []): static
    {
        return new static($status, $data, $headers);
    }

    /**
     * Create a new mock response from a fixture
     *
     * @param string $name
     * @return Fixture
     * @throws DirectoryNotFoundException
     */
    public static function fixture(string $name): Fixture
    {
        return new Fixture($name);
    }

    /**
     * Throw an exception on the request.
     *
     * @param callable|Throwable $value
     * @return $this
     */
    public function throw(callable|Throwable $value): static
    {
        $closure = $value instanceof Throwable ? static fn () => $value : $value;

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
     * Get the headers
     *
     * @return ContentBag
     */
    public function getHeaders(): ContentBag
    {
        return $this->headers;
    }

    /**
     * Get the response body
     *
     * @return BodyRepository
     */
    public function getData(): BodyRepository
    {
        return $this->data;
    }

    /**
     * Get the formatted data on the response.
     *
     * @return string
     */
    public function getDataAsString(): string
    {
        return (string)$this->data;
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
}
