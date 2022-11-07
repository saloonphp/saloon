<?php declare(strict_types=1);

namespace Sammyjo20\Saloon\Helpers;

use Sammyjo20\Saloon\Contracts\SaloonResponse;
use Sammyjo20\Saloon\Http\PendingSaloonRequest;
use Sammyjo20\Saloon\Http\Faking\SimulatedResponsePayload;
use Sammyjo20\Saloon\Contracts\MiddlewarePipeline as MiddlewarePipelineContract;

class MiddlewarePipeline implements MiddlewarePipelineContract
{
    /**
     * Request Pipeline
     *
     * @var Pipeline
     */
    protected Pipeline $requestPipeline;

    /**
     * Response Pipeline
     *
     * @var Pipeline
     */
    protected Pipeline $responsePipeline;

    /**
     * Constructor
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
    public function onRequest(callable $closure): static
    {
        $this->requestPipeline = $this->requestPipeline->pipe(function (PendingSaloonRequest $pendingRequest) use ($closure) {
            $result = $closure($pendingRequest);

            if ($result instanceof PendingSaloonRequest) {
                return $result;
            }

            if ($result instanceof SimulatedResponsePayload) {
                $pendingRequest->setSimulatedResponsePayload($result);
            }

            return $pendingRequest;
        });

        return $this;
    }

    /**
     * Add a middleware after the request is sent
     *
     * @param callable $closure
     * @return $this
     */
    public function onResponse(callable $closure): static
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
     * @param PendingSaloonRequest $pendingRequest
     * @return PendingSaloonRequest
     */
    public function executeRequestPipeline(PendingSaloonRequest $pendingRequest): PendingSaloonRequest
    {
        $this->requestPipeline->process($pendingRequest);

        return $pendingRequest;
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
     * @return Pipeline
     */
    public function getRequestPipeline(): Pipeline
    {
        return $this->requestPipeline;
    }

    /**
     * Get the response pipeline
     *
     * @return Pipeline
     */
    public function getResponsePipeline(): Pipeline
    {
        return $this->responsePipeline;
    }
}
