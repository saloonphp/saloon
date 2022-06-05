<?php

namespace Sammyjo20\Saloon\Traits;

use Sammyjo20\Saloon\Helpers\DataBag;
use Sammyjo20\Saloon\Helpers\ContentBag;
use Sammyjo20\Saloon\Helpers\MiddlewarePipeline;

trait HasRequestProperties
{
    /**
     * Request Headers
     *
     * @var ContentBag
     */
    private ContentBag $headers;

    /**
     * Request Query Parameters
     *
     * @var ContentBag
     */
    private ContentBag $queryParameters;

    /**
     * Request Data
     *
     * @var DataBag
     */
    private DataBag $data;

    /**
     * Request Config
     *
     * @var ContentBag
     */
    private ContentBag $config;

    /**
     * Saloon Middleware Pipeline
     *
     * @var MiddlewarePipeline
     */
    private MiddlewarePipeline $middlewarePipeline;

    /**
     * Retrieve the headers content bag.
     *
     * @return ContentBag
     */
    public function headers(): ContentBag
    {
        return $this->headers ??= new ContentBag($this->defaultHeaders());
    }

    /**
     * Retrieve the query parameters content bag.
     *
     * @return ContentBag
     */
    public function queryParameters(): ContentBag
    {
        return $this->queryParameters ??= new ContentBag($this->defaultQueryParameters());
    }

    /**
     * Retrieve the data content bag.
     *
     * @return DataBag
     */
    public function data(): DataBag
    {
        return $this->data ??= new DataBag($this->defaultData());
    }

    /**
     * Retrieve the config content bag.
     *
     * @return ContentBag
     */
    public function config(): ContentBag
    {
        return $this->config ??= new ContentBag($this->defaultConfig());
    }

    /**
     * Retrieve the guzzle middleware content bag.
     *
     * @return MiddlewarePipeline
     */
    public function middlewarePipeline(): MiddlewarePipeline
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
     * Default Data
     *
     * @return mixed
     */
    protected function defaultData(): mixed
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
