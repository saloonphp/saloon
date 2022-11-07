<?php declare(strict_types=1);

namespace Sammyjo20\Saloon\Traits\RequestProperties;

use Sammyjo20\Saloon\Helpers\MiddlewarePipeline;
use Sammyjo20\Saloon\Contracts\MiddlewarePipeline as MiddlewarePipelineContract;

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
