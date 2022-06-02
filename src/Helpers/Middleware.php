<?php

namespace Sammyjo20\Saloon\Helpers;

use League\Pipeline\Pipeline;
use Sammyjo20\Saloon\Http\SaloonResponse;
use Sammyjo20\Saloon\Http\PendingSaloonRequest;

class Middleware
{
    /**
     * @var Pipeline
     */
    protected Pipeline $requestPipeline;

    /**
     * @var Pipeline
     */
    protected Pipeline $responsePipeline;

    public function __construct()
    {
        $this->requestPipeline = new Pipeline();
        $this->responsePipeline = new Pipeline();
    }

    /**
     * @param callable $closure
     * @return $this
     */
    public function addRequestPipe(callable $closure): self
    {
        $this->requestPipeline = $this->requestPipeline->pipe(function (PendingSaloonRequest $request) use ($closure) {
            $closure($request);

            return $request;
        });

        return $this;
    }

    /**
     * @param callable $closure
     * @return $this
     */
    public function addResponsePipe(callable $closure): self
    {
        $this->responsePipeline = $this->responsePipeline->pipe(function (SaloonResponse $response) use ($closure) {
            $closure($response);

            return $response;
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
        return $this->requestPipeline->process($request);
    }

    /**
     * Process the request pipeline.
     *
     * @param SaloonResponse $response
     * @return SaloonResponse
     */
    public function executeResponsePipeline(SaloonResponse $response): SaloonResponse
    {
        return $this->responsePipeline->process($response);
    }
}
