<?php

declare(strict_types=1);

namespace Saloon\Contracts;

/**
 * @internal
 */
interface HasMiddlewarePipeline
{
    /**
     * Access the middleware pipeline
     */
    public function middleware(): MiddlewarePipeline;
}
