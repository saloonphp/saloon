<?php

namespace Sammyjo20\Saloon\Helpers;

use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\Saloon\Http\PendingSaloonRequest;
use Sammyjo20\Saloon\Http\Responses\SaloonResponse;

class MiddlewarePipeline
{
    /**
     * @var Pipeline
     */
    protected Pipeline $requestPipeline;

    /**
     * @var Pipeline
     */
    protected Pipeline $responsePipeline;

    /**
     * Instantiate the pipelines
     */
    public function __construct()
    {
        $this->requestPipeline = new Pipeline();
        $this->responsePipeline = new Pipeline();
    }

    /**
     * Add a middleware before the request is sent
     *
     * @param callable $closure
     * @return $this
     */
    public function onRequest(callable $closure): self
    {
        $this->requestPipeline = $this->requestPipeline->pipe(function (PendingSaloonRequest $request) use ($closure) {
            $result = $closure($request);

            if ($result instanceof PendingSaloonRequest) {
                return $result;
            }

            if ($result instanceof MockResponse) {
                $request->setMockResponse($result);
            }

            return $request;
        });

        return $this;
    }

    /**
     * Add a middleware after the request is sent
     *
     * @param callable $closure
     * @return $this
     */
    public function onResponse(callable $closure): self
    {
        $this->responsePipeline = $this->responsePipeline->pipe(function (SaloonResponse $response) use ($closure) {
            $result = $closure($response);

            return $result instanceof SaloonResponse ? $result : $response;
        });

        return $this;
    }

    /**
     * Process the request pipeline.
     *
     * @param PendingSaloonRequest $request
     * @return PendingSaloonRequest
     */
    public function executeRequestPipeline(PendingSaloonRequest $request): PendingSaloonRequest
    {
        $this->requestPipeline->process($request);

        return $request;
    }

    /**
     * Process the response pipeline.
     *
     * @param SaloonResponse $response
     * @return SaloonResponse
     */
    public function executeResponsePipeline(SaloonResponse $response): SaloonResponse
    {
        $this->responsePipeline->process($response);

        return $response;
    }

    /**
     * Merge in another middleware pipeline.
     *
     * @param MiddlewarePipeline $middlewarePipeline
     * @return $this
     */
    public function merge(MiddlewarePipeline $middlewarePipeline): self
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
     * @return Pipeline
     */
    public function getRequestPipeline(): Pipeline
    {
        return $this->requestPipeline;
    }

    /**
     * @return Pipeline
     */
    public function getResponsePipeline(): Pipeline
    {
        return $this->responsePipeline;
    }
}
