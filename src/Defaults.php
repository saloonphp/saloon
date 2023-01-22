<?php

namespace Saloon;

use Saloon\Contracts\MiddlewarePipeline as MiddlewarePipelineContract;
use Saloon\Helpers\MiddlewarePipeline;

final class Defaults
{
    /**
     * Middleware Pipeline
     *
     * @var \Saloon\Contracts\MiddlewarePipeline|null
     */
    protected static ?MiddlewarePipelineContract $middlewarePipeline = null;

    /**
     * Update global middleware
     *
     * @return \Saloon\Contracts\MiddlewarePipeline
     */
    public static function middleware(): MiddlewarePipelineContract
    {
        return self::$middlewarePipeline ??= new MiddlewarePipeline;
    }
}
