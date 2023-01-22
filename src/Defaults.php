<?php

declare(strict_types=1);

namespace Saloon;

use Saloon\Helpers\MiddlewarePipeline;
use Saloon\Contracts\MiddlewarePipeline as MiddlewarePipelineContract;

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
