<?php declare(strict_types=1);

namespace Sammyjo20\Saloon\Http\Faking;

use Throwable;
use Psr\Http\Message\RequestInterface;
use Sammyjo20\Saloon\Repositories\ArrayStore;
use Sammyjo20\Saloon\Data\MockExceptionClosure;
use Sammyjo20\Saloon\Contracts\Body\BodyRepository;
use Sammyjo20\Saloon\Repositories\Body\JsonBodyRepository;
use Sammyjo20\Saloon\Exceptions\DirectoryNotFoundException;
use Sammyjo20\Saloon\Repositories\Body\StringBodyRepository;

class SimulatedResponsePayload
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
     * @var ArrayStore
     */
    protected ArrayStore $headers;

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
     * @param array|string $data
     * @param array $headers
     */
    public function __construct(int $status = 200, array|string $data = [], array $headers = [])
    {
        $this->status = $status;
        $this->data = is_array($data) ? new JsonBodyRepository($data) : new StringBodyRepository($data);
        $this->headers = new ArrayStore($headers);
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
     * @return ArrayStore
     */
    public function getHeaders(): ArrayStore
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
