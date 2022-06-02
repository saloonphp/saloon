<?php

namespace Sammyjo20\Saloon\Traits;

use Sammyjo20\Saloon\Data\RequestProperties;
use Sammyjo20\Saloon\Helpers\ContentBag;

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
     * @var ContentBag
     */
    private ContentBag $data;

    /**
     * Request Config
     *
     * @var ContentBag
     */
    private ContentBag $config;

    /**
     * Request Guzzle Middleware
     *
     * @var ContentBag
     */
    private ContentBag $guzzleMiddleware;

    /**
     * Request Response Interceptors
     *
     * @var ContentBag
     */
    private ContentBag $responseInterceptors;

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
     * @return ContentBag
     */
    public function data(): ContentBag
    {
        return $this->data ??= new ContentBag($this->defaultData());
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
     * @return ContentBag
     */
    public function guzzleMiddleware(): ContentBag
    {
        return $this->guzzleMiddleware ??= new ContentBag($this->defaultGuzzleMiddleware());
    }

    /**
     * Retrieve the response interceptors content bag.
     *
     * @return ContentBag
     */
    public function responseInterceptors(): ContentBag
    {
        return $this->responseInterceptors ??= new ContentBag($this->defaultResponseInterceptors());
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
     * @return array
     */
    protected function defaultData(): array
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
     * Default Guzzle Middleware
     *
     * @return array
     */
    protected function defaultGuzzleMiddleware(): array
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

    /**
     * Get all the request properties with their default set.
     *
     * @return RequestProperties
     */
    public function getRequestProperties(): RequestProperties
    {
        return new RequestProperties(
            $this->headers()->all(),
            $this->queryParameters()->all(),
            $this->data()->all(),
            $this->config()->all(),
            $this->guzzleMiddleware()->all(),
            $this->responseInterceptors()->all(),
        );
    }
}
