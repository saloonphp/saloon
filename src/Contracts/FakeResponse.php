<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use Closure;
use Throwable;
use Saloon\Http\Faking\Fixture;
use Saloon\Http\PendingRequest;
use Psr\Http\Message\ResponseInterface;
use Saloon\Contracts\Body\BodyRepository;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\ResponseFactoryInterface;

interface FakeResponse
{
    /**
     * Get the status from the responses
     */
    public function status(): int;

    /**
     * Get the headers
     */
    public function headers(): ArrayStore;

    /**
     * Get the response body
     */
    public function body(): BodyRepository;

    /**
     * Throw an exception on the request.
     *
     * @return $this
     */
    public function throw(Closure|Throwable $value): static;

    /**
     * Get the exception
     */
    public function getException(PendingRequest $pendingRequest): ?Throwable;

    /**
     * Create a new mock response from a fixture
     */
    public static function fixture(string $name): Fixture;

    /**
     * Get the response as a ResponseInterface
     */
    public function createPsrResponse(ResponseFactoryInterface $responseFactory, StreamFactoryInterface $streamFactory): ResponseInterface;
}
