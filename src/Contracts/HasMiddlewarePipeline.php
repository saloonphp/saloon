<?php

declare(strict_types=1);

namespace Saloon\Contracts;

interface HasMiddlewarePipeline
{
    /**
     * Access the middleware pipeline
     *
     * @return \Saloon\Contracts\MiddlewarePipeline
     */
    public function middleware(): MiddlewarePipeline;
}
