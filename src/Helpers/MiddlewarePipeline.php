<?php

declare(strict_types=1);

namespace Saloon\Helpers;

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
     * @param callable $callable
     * @param bool $prepend
     * @param string|null $name
     * @return $this
     * @throws \Saloon\Exceptions\DuplicatePipeNameException
     */
    public function onRequest(callable $callable, bool $prepend = false, ?string $name = null): static
    {
        $this->requestPipeline = $this->requestPipeline->pipe(function (PendingRequest $pendingRequest) use ($callable) {
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
     * @param callable $callable
     * @param bool $prepend
     * @param string|null $name
     * @return $this
     * @throws \Saloon\Exceptions\DuplicatePipeNameException
     */
    public function onResponse(callable $callable, bool $prepend = false, ?string $name = null): static
    {
        $this->responsePipeline = $this->responsePipeline->pipe(function (Response $response) use ($callable) {
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
