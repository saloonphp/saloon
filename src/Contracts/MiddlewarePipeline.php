<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use Saloon\Helpers\Pipeline;

interface MiddlewarePipeline
{
    /**
     * Add a middleware before the request is sent
     *
     * @param callable $closure
     * @return \Sammyjo20\Saloon\Helpers\MiddlewarePipeline
     */
    public function onRequest(callable $closure): static;

    /**
     * Add a middleware after the request is sent
     *
     * @param callable $closure
     * @return \Sammyjo20\Saloon\Helpers\MiddlewarePipeline
     */
    public function onResponse(callable $closure): static;

    /**
     * Process the request pipeline.
     *
     * @param PendingRequest $pendingRequest
     * @return PendingRequest
     */
    public function executeRequestPipeline(PendingRequest $pendingRequest): PendingRequest;

    /**
     * Process the response pipeline.
     *
     * @param Response $response
     * @return Response
     */
    public function executeResponsePipeline(Response $response): Response;

    /**
     * Merge in another middleware pipeline.
     *
     * @param MiddlewarePipeline $middlewarePipeline
     * @return $this
     */
    public function merge(self $middlewarePipeline): static;

    /**
     * Get the request pipeline
     *
     * @return Pipeline
     */
    public function getRequestPipeline(): Pipeline;

    /**
     * Get the response pipeline
     *
     * @return Pipeline
     */
    public function getResponsePipeline(): Pipeline;
}
