<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use Saloon\Enums\Method;

interface Request extends Authenticatable, CanThrowRequestExceptions, HasConfig, HasHeaders, HasQueryParams, HasDelay, HasMiddlewarePipeline, HasMockClient
{
    /**
     * Get the HTTP method
     *
     * @return \Saloon\Enums\Method
     */
    public function getMethod(): Method;

    /**
     * Define the endpoint for the request.
     *
     * @return string
     */
    public function resolveEndpoint(): string;

    /**
     * Handle the boot lifecycle hook
     *
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     * @return void
     */
    public function boot(PendingRequest $pendingRequest): void;

    /**
     * Cast the response to a DTO.
     *
     * @param \Saloon\Contracts\Response $response
     * @return mixed
     */
    public function createDtoFromResponse(Response $response): mixed;

    /**
     * Get the response class
     *
     * @return class-string<\Saloon\Contracts\Response>|null
     */
    public function resolveResponseClass(): ?string;
}
