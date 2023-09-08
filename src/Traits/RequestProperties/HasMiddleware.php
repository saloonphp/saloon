<?php

declare(strict_types=1);

namespace Saloon\Traits\RequestProperties;

use Saloon\Helpers\MiddlewarePipeline;

trait HasMiddleware
{
    /**
     * Middleware Pipeline
     */
    protected MiddlewarePipeline $middlewarePipeline;

    /**
     * Access the middleware pipeline
     */
    public function middleware(): MiddlewarePipeline
    {
        return $this->middlewarePipeline ??= new MiddlewarePipeline;
    }
}
