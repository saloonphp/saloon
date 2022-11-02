<?php declare(strict_types=1);

namespace Sammyjo20\Saloon\Traits;

use Sammyjo20\Saloon\Repositories\ArrayStore;
use Sammyjo20\Saloon\Helpers\MiddlewarePipeline;
use Sammyjo20\Saloon\Contracts\ArrayStore as ArrayStoreContract;
use Sammyjo20\Saloon\Contracts\MiddlewarePipeline as MiddlewarePipelineContract;

trait HasRequestProperties
{
    /**
     * Request Headers
     *
     * @var ArrayStoreContract
     */
    protected ArrayStoreContract $headers;

    /**
     * Request Query Parameters
     *
     * @var ArrayStoreContract
     */
    protected ArrayStoreContract $queryParameters;

    /**
     * Request Config
     *
     * @var ArrayStoreContract
     */
    protected ArrayStoreContract $config;

    /**
     * Middleware Pipeline
     *
     * @var MiddlewarePipelineContract
     */
    protected MiddlewarePipelineContract $middlewarePipeline;

    /**
     * Access the headers
     *
     * @return ArrayStoreContract
     */
    public function headers(): ArrayStoreContract
    {
        return $this->headers ??= new ArrayStore($this->defaultHeaders());
    }

    /**
     * Access the query parameters
     *
     * @return ArrayStoreContract
     */
    public function queryParameters(): ArrayStoreContract
    {
        return $this->queryParameters ??= new ArrayStore($this->defaultQueryParameters());
    }

    /**
     * Access the config
     *
     * @return ArrayStoreContract
     */
    public function config(): ArrayStoreContract
    {
        return $this->config ??= new ArrayStore($this->defaultConfig());
    }

    /**
     * Access the middleware pipeline
     *
     * @return MiddlewarePipelineContract
     */
    public function middleware(): MiddlewarePipelineContract
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
