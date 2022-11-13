<?php declare(strict_types=1);

namespace Saloon\Traits\RequestProperties;

use Saloon\Helpers\MiddlewarePipeline;
use Saloon\Contracts\MiddlewarePipeline as MiddlewarePipelineContract;

trait HasMiddleware
{
    /**
     * Middleware Pipeline
     *
     * @var MiddlewarePipelineContract
     */
    protected MiddlewarePipelineContract $middlewarePipeline;

    /**
     * Access the middleware pipeline
     *
     * @return MiddlewarePipelineContract
     */
    public function middleware(): MiddlewarePipelineContract
    {
        return $this->middlewarePipeline ??= new MiddlewarePipeline;
    }
}
