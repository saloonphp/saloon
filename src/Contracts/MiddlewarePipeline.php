<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use Saloon\Data\PipeOrder;

/**
 * @internal
 */
interface MiddlewarePipeline
{
    /**
     * Add a middleware before the request is sent
     *
     * @param callable(\Saloon\Contracts\PendingRequest): (\Saloon\Contracts\PendingRequest|\Saloon\Contracts\FakeResponse|void) $callable
     * @return $this
     */
    public function onRequest(callable $callable, ?string $name = null, ?PipeOrder $order = null): static;

    /**
     * Add a middleware after the request is sent
     *
     * @param callable(\Saloon\Contracts\Response): (\Saloon\Contracts\Response|void) $callable
     * @return $this
     */
    public function onResponse(callable $callable, ?string $name = null, ?PipeOrder $order = null): static;

    /**
     * Process the request pipeline.
     *
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     * @return \Saloon\Contracts\PendingRequest
     */
    public function executeRequestPipeline(PendingRequest $pendingRequest): PendingRequest;

    /**
     * Process the response pipeline.
     *
     * @param \Saloon\Contracts\Response $response
     * @return \Saloon\Contracts\Response
     */
    public function executeResponsePipeline(Response $response): Response;

    /**
     * Merge in another middleware pipeline.
     *
     * @param \Saloon\Contracts\MiddlewarePipeline $middlewarePipeline
     * @return $this
     */
    public function merge(self $middlewarePipeline): static;

    /**
     * Get the request pipeline
     *
     * @return \Saloon\Contracts\Pipeline
     */
    public function getRequestPipeline(): Pipeline;

    /**
     * Get the response pipeline
     *
     * @return \Saloon\Contracts\Pipeline
     */
    public function getResponsePipeline(): Pipeline;
}
