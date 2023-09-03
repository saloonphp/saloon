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
     *
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     */
    public function boot(PendingRequest $pendingRequest): void;

    /**
     * Handle the PSR request before it is sent
     */
    public function handlePsrRequest(RequestInterface $request, PendingRequest $pendingRequest): RequestInterface;

    /**
     * Cast the response to a DTO.
     *
     * @param \Saloon\Contracts\Response $response
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
     * @return \Saloon\Contracts\Pool
     */
    public function pool(iterable|callable $requests = [], int|callable $concurrency = 5, callable|null $responseHandler = null, callable|null $exceptionHandler = null): Pool;

    /**
     * Manage the request sender.
     *
     * @return \Saloon\Contracts\Sender
     */
    public function sender(): Sender;

    /**
     * Send a request
     *
     * @param \Saloon\Contracts\Request $request
     * @param \Saloon\Contracts\MockClient|null $mockClient
     * @return \Saloon\Contracts\Response
     */
    public function send(Request $request, MockClient $mockClient = null): Response;

    /**
     * Send a synchronous request and retry if it fails
     *
     * @param \Saloon\Contracts\Request $request
     * @param int $tries
     * @param int $interval
     * @param callable(\Throwable, \Saloon\Contracts\PendingRequest): (bool)|null $handleRetry
     * @param bool $throw
     * @param \Saloon\Contracts\MockClient|null $mockClient
     * @return \Saloon\Contracts\Response
     */
    public function sendAndRetry(Request $request, int $tries, int $interval = 0, callable $handleRetry = null, bool $throw = false, MockClient $mockClient = null): Response;

    /**
     * Send a request asynchronously
     *
     * @param \Saloon\Contracts\Request $request
     * @param \Saloon\Contracts\MockClient|null $mockClient
     */
    public function sendAsync(Request $request, MockClient $mockClient = null): PromiseInterface;

    /**
     * Create a new PendingRequest
     *
     * @param \Saloon\Contracts\Request $request
     * @param \Saloon\Contracts\MockClient|null $mockClient
     * @return \Saloon\Contracts\PendingRequest
     */
    public function createPendingRequest(Request $request, MockClient $mockClient = null): PendingRequest;
}
