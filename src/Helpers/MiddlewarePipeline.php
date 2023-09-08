<?php

declare(strict_types=1);

namespace Saloon\Helpers;

use Saloon\Http\Response;
use Saloon\Enums\PipeOrder;
use Saloon\Http\PendingRequest;
use Saloon\Contracts\FakeResponse;

class MiddlewarePipeline
{
    /**
     * Request Pipeline
     */
    protected Pipeline $requestPipeline;

    /**
     * Response Pipeline
     */
    protected Pipeline $responsePipeline;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->requestPipeline = new Pipeline;
        $this->responsePipeline = new Pipeline;
    }

    /**
     * Add a middleware before the request is sent
     *
     * @param callable(\Saloon\Http\PendingRequest): (\Saloon\Http\PendingRequest|\Saloon\Contracts\FakeResponse|void) $callable
     * @return $this
     * @throws \Saloon\Exceptions\DuplicatePipeNameException
     */
    public function onRequest(callable $callable, ?string $name = null, ?PipeOrder $order = null): static
    {
        /**
         * For some reason, PHP is not destructing non-static Closures, or 'things' using non-static Closures, correctly, keeping unused objects intact.
         * Using a *static* Closure, or re-binding it to an empty, anonymous class/object is a workaround for the issue.
         * If we don't, things using the MiddlewarePipeline, in turn, won't destruct.
         * Concretely speaking, for Saloon, this means that the Connector will *not* get destructed, and thereby also not the underlying client.
         * Which in turn leaves open file handles until the process terminates.
         *
         * Do note that this is entirely about our *wrapping* Closure below.
         * The provided callable doesn't affect the MiddlewarePipeline.
         */
        $this->requestPipeline->pipe(static function (PendingRequest $pendingRequest) use ($callable): PendingRequest {
            $result = $callable($pendingRequest);

            if ($result instanceof PendingRequest) {
                return $result;
            }

            if ($result instanceof FakeResponse) {
                $pendingRequest->setFakeResponse($result);
            }

            return $pendingRequest;
        }, $name, $order);

        return $this;
    }

    /**
     * Add a middleware after the request is sent
     *
     * @param callable(\Saloon\Http\Response): (\Saloon\Http\Response|void) $callable
     * @return $this
     * @throws \Saloon\Exceptions\DuplicatePipeNameException
     */
    public function onResponse(callable $callable, ?string $name = null, ?PipeOrder $order = null): static
    {
        /**
         * For some reason, PHP is not destructing non-static Closures, or 'things' using non-static Closures, correctly, keeping unused objects intact.
         * Using a *static* Closure, or re-binding it to an empty, anonymous class/object is a workaround for the issue.
         * If we don't, things using the MiddlewarePipeline, in turn, won't destruct.
         * Concretely speaking, for Saloon, this means that the Connector will *not* get destructed, and thereby also not the underlying client.
         * Which in turn leaves open file handles until the process terminates.
         *
         * Do note that this is entirely about our *wrapping* Closure below.
         * The provided callable doesn't affect the MiddlewarePipeline.
         */
        $this->responsePipeline->pipe(static function (Response $response) use ($callable): Response {
            $result = $callable($response);

            return $result instanceof Response ? $result : $response;
        }, $name, $order);

        return $this;
    }

    /**
     * Process the request pipeline.
     */
    public function executeRequestPipeline(PendingRequest $pendingRequest): PendingRequest
    {
        return $this->requestPipeline->process($pendingRequest);
    }

    /**
     * Process the response pipeline.
     */
    public function executeResponsePipeline(Response $response): Response
    {
        return $this->responsePipeline->process($response);
    }

    /**
     * Merge in another middleware pipeline.
     *
     * @return $this
     * @throws \Saloon\Exceptions\DuplicatePipeNameException
     */
    public function merge(MiddlewarePipeline $middlewarePipeline): static
    {
        $requestPipes = array_merge(
            $this->getRequestPipeline()->getPipes(),
            $middlewarePipeline->getRequestPipeline()->getPipes()
        );

        $responsePipes = array_merge(
            $this->getResponsePipeline()->getPipes(),
            $middlewarePipeline->getResponsePipeline()->getPipes()
        );

        $this->requestPipeline->setPipes($requestPipes);
        $this->responsePipeline->setPipes($responsePipes);

        return $this;
    }

    /**
     * Get the request pipeline
     */
    public function getRequestPipeline(): Pipeline
    {
        return $this->requestPipeline;
    }

    /**
     * Get the response pipeline
     */
    public function getResponsePipeline(): Pipeline
    {
        return $this->responsePipeline;
    }
}
