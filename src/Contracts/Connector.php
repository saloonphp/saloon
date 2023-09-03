<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Promise\PromiseInterface;

/**
 * @internal
 */
interface Connector extends Authenticatable, CanThrowRequestExceptions, HasConfig, HasHeaders, HasQueryParams, HasDelay, HasMiddlewarePipeline, HasMockClient, HasRetry
{
    /**
     * Handle the boot lifecycle hook
     */
    public function boot(PendingRequest $pendingRequest): void;

    /**
     * Handle the PSR request before it is sent
     */
    public function handlePsrRequest(RequestInterface $request, PendingRequest $pendingRequest): RequestInterface;

    /**
     * Cast the response to a DTO.
     */
    public function createDtoFromResponse(Response $response): mixed;

    /**
     * Define the base URL of the API.
     */
    public function resolveBaseUrl(): string;

    /**
     * Get the response class
     *
     * @return class-string<\Saloon\Contracts\Response>|null
     */
    public function resolveResponseClass(): ?string;

    /**
     * Create a request pool
     *
     * @template TKey of array-key
     *
     * @param iterable<\GuzzleHttp\Promise\PromiseInterface|\Saloon\Contracts\Request>|callable(\Saloon\Contracts\Connector): iterable<\GuzzleHttp\Promise\PromiseInterface|\Saloon\Contracts\Request> $requests
     * @param int|callable(int $pendingRequests): (int) $concurrency
     * @param callable(\Saloon\Contracts\Response, TKey $key, \GuzzleHttp\Promise\PromiseInterface $poolAggregate): (void)|null $responseHandler
     * @param callable(mixed $reason, TKey $key, \GuzzleHttp\Promise\PromiseInterface $poolAggregate): (void)|null $exceptionHandler
     */
    public function pool(iterable|callable $requests = [], int|callable $concurrency = 5, callable|null $responseHandler = null, callable|null $exceptionHandler = null): Pool;

    /**
     * Manage the request sender.
     */
    public function sender(): Sender;

    /**
     * Send a request
     */
    public function send(Request $request, MockClient $mockClient = null): Response;

    /**
     * Send a synchronous request and retry if it fails
     *
     * @param callable(\Throwable, \Saloon\Contracts\PendingRequest): (bool)|null $handleRetry
     */
    public function sendAndRetry(Request $request, int $tries, int $interval = 0, callable $handleRetry = null, bool $throw = false, MockClient $mockClient = null): Response;

    /**
     * Send a request asynchronously
     */
    public function sendAsync(Request $request, MockClient $mockClient = null): PromiseInterface;

    /**
     * Create a new PendingRequest
     */
    public function createPendingRequest(Request $request, MockClient $mockClient = null): PendingRequest;
}
