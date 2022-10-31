<?php

namespace Sammyjo20\Saloon\Traits;

use Sammyjo20\Saloon\Helpers\MiddlewarePipeline;
use Sammyjo20\Saloon\Repositories\ArrayStore;

trait HasRequestProperties
{
    /**
     * Request Headers
     *
     * @var ArrayStore
     */
    protected ArrayStore $headers;

    /**
     * Request Query Parameters
     *
     * @var ArrayStore
     */
    protected ArrayStore $queryParameters;

    /**
     * Request Config
     *
     * @var ArrayStore
     */
    protected ArrayStore $config;

    /**
     * Saloon Middleware Pipeline
     *
     * @var MiddlewarePipeline
     */
    protected MiddlewarePipeline $middlewarePipeline;

    /**
     * Retrieve the headers content bag.
     *
     * @return ArrayStore
     */
    public function headers(): ArrayStore
    {
        return $this->headers ??= new ArrayStore($this->defaultHeaders());
    }

    /**
     * Retrieve the query parameters content bag.
     *
     * @return ArrayStore
     */
    public function queryParameters(): ArrayStore
    {
        return $this->queryParameters ??= new ArrayStore($this->defaultQueryParameters());
    }

    /**
     * Retrieve the config content bag.
     *
     * @return ArrayStore
     */
    public function config(): ArrayStore
    {
        return $this->config ??= new ArrayStore($this->defaultConfig());
    }

    /**
     * Retrieve the guzzle middleware content bag.
     *
     * @return MiddlewarePipeline
     */
    public function middleware(): MiddlewarePipeline
    {
        return $this->middlewarePipeline ??= new MiddlewarePipeline;
    }

    /**
     * Default Request Headers
     *
     * @return array
     */
    protected function defaultHeaders(): array
    {
        return [];
    }

    /**
     * Default Query Parameters
     *
     * @return array
     */
    protected function defaultQueryParameters(): array
    {
        return [];
    }

    /**
     * Default Config
     *
     * @return array
     */
    protected function defaultConfig(): array
    {
        return [];
    }
}
