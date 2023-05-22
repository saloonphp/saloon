<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use Closure;
use Throwable;
use Saloon\Http\Faking\Fixture;
use Psr\Http\Message\ResponseInterface;
use Saloon\Contracts\Body\BodyRepository;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;

/**
 * @method static static make(mixed $body = [], int $status = 200, array $headers = []) Create a new mock response
 */
interface FakeResponse
{
    /**
     * Create a new mock response from a fixture
     *
     * @param string $name
     * @return \Saloon\Http\Faking\Fixture
     * @throws \Saloon\Exceptions\DirectoryNotFoundException
     */
    public static function fixture(string $name): Fixture;

    /**
     * Get the status from the responses
     *
     * @return int
     */
    public function getStatus(): int;

    /**
     * Get the headers
     *
     * @return \Saloon\Contracts\ArrayStore
     */
    public function headers(): ArrayStore;

    /**
     * Get the response body
     *
     * @return \Saloon\Contracts\Body\BodyRepository
     */
    public function body(): BodyRepository;

    /**
     * Get the formatted body on the response.
     *
     * @return string
     */
    public function getBodyAsString(): string;

    /**
     * Throw an exception on the request.
     *
     * @param \Closure|\Throwable $value
     * @return $this
     */
    public function throw(Closure|Throwable $value): static;

    /**
     * Checks if the response throws an exception.
     *
     * @return bool
     */
    public function throwsException(): bool;

    /**
     * Invoke the exception.
     *
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     * @return \Throwable|null
     */
    public function getException(PendingRequest $pendingRequest): ?Throwable;

    /**
     * Get the response as a ResponseInterface
     *
     * @param ResponseFactoryInterface $responseFactory
     * @param StreamFactoryInterface $streamFactory
     * @return ResponseInterface
     */
    public function createPsrResponse(ResponseFactoryInterface $responseFactory, StreamFactoryInterface $streamFactory): ResponseInterface;
}
