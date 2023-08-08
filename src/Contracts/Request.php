<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use Saloon\Enums\Method;
use Psr\Http\Message\RequestInterface;

interface Request extends Authenticatable, CanThrowRequestExceptions, HasConfig, HasHeaders, HasQueryParams, HasDelay, HasMiddlewarePipeline, HasMockClient
{
    /**
     * Get the HTTP method
     */
    public function getMethod(): Method;

    /**
     * Define the endpoint for the request.
     */
    public function resolveEndpoint(): string;

    /**
     * Handle the boot lifecycle hook
     *
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     */
    public function boot(PendingRequest $pendingRequest): void;

    /**
     * Handle the PSR request before it is sent
     *
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     */
    public function handlePsrRequest(RequestInterface $request, PendingRequest $pendingRequest): RequestInterface;

    /**
     * Cast the response to a DTO.
     *
     * @param \Saloon\Contracts\Response $response
     */
    public function createDtoFromResponse(Response $response): mixed;

    /**
     * Get the response class
     *
     * @return class-string<\Saloon\Contracts\Response>|null
     */
    public function resolveResponseClass(): ?string;
}
