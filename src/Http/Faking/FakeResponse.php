<?php

declare(strict_types=1);

namespace Saloon\Http\Faking;

use Closure;
use Throwable;
use Saloon\Traits\Makeable;
use Saloon\Repositories\ArrayStore;
use Saloon\Contracts\PendingRequest;
use Psr\Http\Message\ResponseInterface;
use Saloon\Contracts\Body\BodyRepository;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Saloon\Repositories\Body\JsonBodyRepository;
use Saloon\Repositories\Body\StringBodyRepository;
use Saloon\Contracts\ArrayStore as ArrayStoreContract;
use Saloon\Contracts\FakeResponse as FakeResponseContract;

class FakeResponse implements FakeResponseContract
{
    use Makeable;

    /**
     * HTTP Status Code
     *
     * @var int
     */
    protected int $status;

    /**
     * Headers
     *
     * @var \Saloon\Contracts\ArrayStore
     */
    protected ArrayStoreContract $headers;

    /**
     * Request Body
     *
     * @var \Saloon\Contracts\Body\BodyRepository
     */
    protected BodyRepository $body;

    /**
     * Exception Closure
     *
     * @var \Closure|null
     */
    protected ?Closure $responseException = null;

    /**
     * Create a new mock response
     *
     * @param array<string, mixed>|string $body
     * @param int $status
     * @param array<string, mixed> $headers
     */
    public function __construct(array|string $body = [], int $status = 200, array $headers = [])
    {
        $this->body = is_array($body) ? new JsonBodyRepository($body) : new StringBodyRepository($body);
        $this->status = $status;
        $this->headers = new ArrayStore($headers);
    }

    /**
     * Create a new mock response from a fixture
     *
     * @param string $name
     * @return \Saloon\Http\Faking\Fixture
     * @throws \Saloon\Exceptions\DirectoryNotFoundException
     */
    public static function fixture(string $name): Fixture
    {
        return new Fixture($name);
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
     * @return \Saloon\Contracts\ArrayStore
     */
    public function headers(): ArrayStoreContract
    {
        return $this->headers;
    }

    /**
     * Get the response body
     *
     * @return \Saloon\Contracts\Body\BodyRepository
     */
    public function body(): BodyRepository
    {
        return $this->body;
    }

    /**
     * Get the formatted body on the response.
     *
     * @return string
     */
    public function getBodyAsString(): string
    {
        return (string)$this->body;
    }

    /**
     * Throw an exception on the request.
     *
     * @param \Closure|\Throwable $value
     * @return $this
     */
    public function throw(Closure|Throwable $value): static
    {
        $closure = $value instanceof Throwable ? static fn () => $value : $value;

        $this->responseException = $closure;

        return $this;
    }

    /**
     * Checks if the response throws an exception.
     *
     * @return bool
     */
    public function throwsException(): bool
    {
        return $this->responseException instanceof Closure;
    }

    /**
     * Invoke the exception.
     *
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     * @return \Throwable|null
     */
    public function getException(PendingRequest $pendingRequest): ?Throwable
    {
        if (! $this->throwsException()) {
            return null;
        }

        return call_user_func($this->responseException, $pendingRequest);
    }

    /**
     * Get the response as a ResponseInterface
     *
     * @param ResponseFactoryInterface $responseFactory
     * @param StreamFactoryInterface $streamFactory
     * @return ResponseInterface
     */
    public function createPsrResponse(ResponseFactoryInterface $responseFactory, StreamFactoryInterface $streamFactory): ResponseInterface
    {
        $response = $responseFactory->createResponse($this->getStatus());

        foreach ($this->headers()->all() as $headerName => $headerValue) {
            $response = $response->withHeader($headerName, $headerValue);
        }

        return $response->withBody($streamFactory->createStream($this->getBodyAsString()));
    }
}
