<?php

declare(strict_types=1);

namespace Saloon\Helpers;

use Closure;
use Saloon\Contracts\Response;
use Saloon\Contracts\PendingRequest;
use Saloon\Contracts\SimulatedResponsePayload;
use Saloon\Contracts\Pipeline as PipelineContract;
use Saloon\Contracts\MiddlewarePipeline as MiddlewarePipelineContract;

class MiddlewarePipeline implements MiddlewarePipelineContract
{
    /**
     * Request Pipeline
     *
     * @var \Saloon\Contracts\Pipeline
     */
    protected PipelineContract $requestPipeline;

    /**
     * Response Pipeline
     *
     * @var \Saloon\Contracts\Pipeline
     */
    protected PipelineContract $responsePipeline;

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
     * @param callable(\Saloon\Contracts\PendingRequest): (\Saloon\Contracts\PendingRequest|\Saloon\Contracts\SimulatedResponsePayload|void) $callable
     * @param bool $prepend
     * @param string|null $name
     * @return $this
     * @throws \Saloon\Exceptions\DuplicatePipeNameException
     */
    public function onRequest(callable $callable, bool $prepend = false, ?string $name = null): static
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

            if ($result instanceof SimulatedResponsePayload) {
                $pendingRequest->setSimulatedResponsePayload($result);
            }

            return $pendingRequest;
        }, $prepend, $name);

        return $this;
    }

    /**
     * Add a middleware after the request is sent
     *
     * @param callable(\Saloon\Contracts\Response): (\Saloon\Contracts\Response|void) $callable
     * @param bool $prepend
     * @param string|null $name
     * @return $this
     * @throws \Saloon\Exceptions\DuplicatePipeNameException
     */
    public function onResponse(callable $callable, bool $prepend = false, ?string $name = null): static
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
        }, $prepend, $name);

        return $this;
    }

    /**
     * Process the request pipeline.
     *
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     * @return \Saloon\Contracts\PendingRequest
     */
    public function executeRequestPipeline(PendingRequest $pendingRequest): PendingRequest
    {
        return $this->requestPipeline->process($pendingRequest);
    }

    /**
     * Process the response pipeline.
     *
     * @param \Saloon\Contracts\Response $response
     * @return \Saloon\Contracts\Response
     */
    public function executeResponsePipeline(Response $response): Response
    {
        return $this->responsePipeline->process($response);
    }

    /**
     * Merge in another middleware pipeline.
     *
     * @param \Saloon\Contracts\MiddlewarePipeline $middlewarePipeline
     * @return $this
     * @throws \Saloon\Exceptions\DuplicatePipeNameException
     */
    public function merge(MiddlewarePipelineContract $middlewarePipeline): static
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
     *
     * @return \Saloon\Contracts\Pipeline
     */
    public function getRequestPipeline(): PipelineContract
    {
        return $this->requestPipeline;
    }

    /**
     * Get the response pipeline
     *
     * @return \Saloon\Contracts\Pipeline
     */
    public function getResponsePipeline(): PipelineContract
    {
        return $this->responsePipeline;
    }
}
