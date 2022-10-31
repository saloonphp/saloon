<?php

namespace Sammyjo20\Saloon\Traits;

use Sammyjo20\Saloon\Helpers\MiddlewarePipeline;
use Sammyjo20\Saloon\Repositories\ArrayRepository;

trait HasRequestProperties
{
    /**
     * Request Headers
     *
     * @var ArrayRepository
     */
    protected ArrayRepository $headers;

    /**
     * Request Query Parameters
     *
     * @var ArrayRepository
     */
    protected ArrayRepository $queryParameters;

    /**
     * Request Config
     *
     * @var ArrayRepository
     */
    protected ArrayRepository $config;

    /**
     * Saloon Middleware Pipeline
     *
     * @var MiddlewarePipeline
     */
    protected MiddlewarePipeline $middlewarePipeline;

    /**
     * Retrieve the headers content bag.
     *
     * @return ArrayRepository
     */
    public function headers(): ArrayRepository
    {
        return $this->headers ??= new ArrayRepository($this->defaultHeaders());
    }

    /**
     * Retrieve the query parameters content bag.
     *
     * @return ArrayRepository
     */
    public function queryParameters(): ArrayRepository
    {
        return $this->queryParameters ??= new ArrayRepository($this->defaultQueryParameters());
    }

    /**
     * Retrieve the config content bag.
     *
     * @return ArrayRepository
     */
    public function config(): ArrayRepository
    {
        return $this->config ??= new ArrayRepository($this->defaultConfig());
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
