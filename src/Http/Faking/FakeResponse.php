<?php

declare(strict_types=1);

namespace Saloon\Http\Faking;

use Closure;
use Throwable;
use Saloon\Traits\Makeable;
use Saloon\Http\PendingRequest;
use Saloon\Repositories\ArrayStore;
use Psr\Http\Message\ResponseInterface;
use Saloon\Contracts\Body\BodyRepository;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;
use Saloon\Repositories\Body\JsonBodyRepository;
use Saloon\Repositories\Body\StringBodyRepository;
use Saloon\Contracts\ArrayStore as ArrayStoreContract;
use Saloon\Contracts\FakeResponse as FakeResponseContract;

/**
 * @method static static make(mixed $body = [], int $status = 200, array $headers = [])
 */
class FakeResponse implements FakeResponseContract
{
    use Makeable;

    /**
     * HTTP Status Code
     */
    protected int $status;

    /**
     * Headers
     */
    protected ArrayStoreContract $headers;

    /**
     * Request Body
     */
    protected BodyRepository $body;

    /**
     * Exception Closure
     */
    protected ?Closure $responseException = null;

    /**
     * Create a new mock response
     *
     * @param array<string, mixed>|string $body
     * @param array<string, mixed> $headers
     */
    public function __construct(array|string $body = [], int $status = 200, array $headers = [])
    {
        $this->body = is_array($body) ? new JsonBodyRepository($body) : new StringBodyRepository($body);
        $this->status = $status;
        $this->headers = new ArrayStore($headers);
    }

    /**
     *  Get the response body
     */
    public function body(): BodyRepository
    {
        return $this->body;
    }

    /**
     * Get the status from the responses
     */
    public function status(): int
    {
        return $this->status;
    }

    /**
     * Get the headers
     */
    public function headers(): ArrayStoreContract
    {
        return $this->headers;
    }

    /**
     * Throw an exception on the request.
     *
     * @return $this
     */
    public function throw(Closure|Throwable $value): static
    {
        $closure = $value instanceof Throwable ? static fn () => $value : $value;

        $this->responseException = $closure;

        return $this;
    }

    /**
     * Invoke the exception.
     */
    public function getException(PendingRequest $pendingRequest): ?Throwable
    {
        if (! $this->responseException instanceof Closure) {
            return null;
        }

        return call_user_func($this->responseException, $pendingRequest);
    }

    /**
     * Create a new mock response from a fixture
     */
    public static function fixture(string $name): Fixture
    {
        return new Fixture($name);
    }

    /**
     * Get the response as a ResponseInterface
     */
    public function createPsrResponse(ResponseFactoryInterface $responseFactory, StreamFactoryInterface $streamFactory): ResponseInterface
    {
        $response = $responseFactory->createResponse($this->status());

        foreach ($this->headers()->all() as $headerName => $headerValue) {
            $response = $response->withHeader($headerName, $headerValue);
        }

        return $response->withBody($this->body()->toStream($streamFactory));
    }
}
