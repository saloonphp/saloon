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
    public ContentBag $headers;

    /**
     * Request Query Parameters
     *
     * @var ContentBag
     */
    public ContentBag $queryParameters;

    /**
     * Request Data
     *
     * @var ContentBag
     */
    public ContentBag $data;

    /**
     * Request Config
     *
     * @var ContentBag
     */
    public ContentBag $config;

    /**
     * Request Guzzle Middleware
     *
     * @var ContentBag
     */
    public ContentBag $guzzleMiddleware;

    /**
     * Request Response Interceptors
     *
     * @var ContentBag
     */
    public ContentBag $responseInterceptors;

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
}
