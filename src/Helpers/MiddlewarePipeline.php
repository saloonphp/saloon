<?php

declare(strict_types=1);

namespace Saloon\Helpers;

use Saloon\Http\Response;
use Saloon\Enums\PipeOrder;
use Saloon\Http\PendingRequest;
use Saloon\Contracts\FakeResponse;
use Saloon\Exceptions\Request\FatalRequestException;

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
     * Fatal Pipeline
     */
    protected Pipeline $fatalPipeline;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->requestPipeline = new Pipeline;
        $this->responsePipeline = new Pipeline;
        $this->fatalPipeline = new Pipeline;
    }

    /**
     * Deep-clone internal pipelines so cloned requests do not share pipe state.
     */
    public function __clone(): void
    {
        $this->requestPipeline = clone $this->requestPipeline;
        $this->responsePipeline = clone $this->responsePipeline;
        $this->fatalPipeline = clone $this->fatalPipeline;
    }

    /**
     * Add a middleware before the request is sent
     *
     * @param callable(\Saloon\Http\PendingRequest): (\Saloon\Http\PendingRequest|\Saloon\Contracts\FakeResponse|void) $callable
     * @return $this
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
     * Add a middleware to run on fatal errors
     *
     * @param callable(FatalRequestException): (void) $callable
     * @return $this
     */
    public function onFatalException(callable $callable, ?string $name = null, ?PipeOrder $order = null): static
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
        $this->fatalPipeline->pipe(static function (FatalRequestException $throwable) use ($callable): FatalRequestException {
            $callable($throwable);

            return $throwable;
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
     * Process the fatal pipeline.
     * @throws \Saloon\Exceptions\Request\FatalRequestException
     */
    public function executeFatalPipeline(FatalRequestException $throwable): void
    {
        $this->fatalPipeline->process($throwable);
    }

    /**
     * Merge in another middleware pipeline.
     *
     * @return $this
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

        $fatalPipes = array_merge(
            $this->getFatalPipeline()->getPipes(),
            $middlewarePipeline->getFatalPipeline()->getPipes()
        );

        $this->requestPipeline->setPipes($requestPipes);
        $this->responsePipeline->setPipes($responsePipes);
        $this->fatalPipeline->setPipes($fatalPipes);

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

    /**
     * Get the fatal pipeline
     */
    public function getFatalPipeline(): Pipeline
    {
        return $this->fatalPipeline;
    }
}
