<?php

namespace Sammyjo20\Saloon\Traits;

use Sammyjo20\Saloon\Data\RequestProperties;
use Sammyjo20\Saloon\Helpers\ContentBag;
use Sammyjo20\Saloon\Helpers\DataBag;
use Sammyjo20\Saloon\Helpers\Middleware;

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
     * Request Guzzle Middleware
     *
     * @var Middleware
     */
    private Middleware $middleware;

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
     * @return Middleware
     */
    public function middleware(): Middleware
    {
        return $this->middleware ??= new Middleware;
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
     * @return array|string
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

    /**
     * Default Response Interceptors
     *
     * @return array
     */
    protected function defaultResponseInterceptors(): array
    {
        return [];
    }
}
