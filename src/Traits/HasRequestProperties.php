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

    /**
     * Seed each of the request properties with default data.
     *
     * @return $this
     */
    private function setDefaultRequestProperties(): self
    {
        $this->headers = new ContentBag($this->defaultHeaders());
        $this->queryParameters = new ContentBag($this->defaultQueryParameters());
        $this->data = new ContentBag($this->defaultData());
        $this->config = new ContentBag($this->defaultConfig());
        $this->guzzleMiddleware = new ContentBag($this->defaultGuzzleMiddleware());
        $this->responseInterceptors = new ContentBag($this->defaultResponseInterceptors());

        return $this;
    }

    /**
     * Convert all the request properties into a DTO.
     *
     * @return RequestProperties
     */
    public function getRequestProperties(): RequestProperties
    {
        return new RequestProperties(
            $this->headers,
            $this->queryParameters,
            $this->data,
            $this->config,
            $this->guzzleMiddleware,
            $this->responseInterceptors,
        );
    }

    /**
     * Merge request properties together.
     *
     * @param RequestProperties $requestProperties
     * @return $this
     */
    private function mergeRequestProperties(RequestProperties $requestProperties): self
    {
        $this->headers->merge($requestProperties->headers->all());
        $this->queryParameters->merge($requestProperties->queryParameters->all());
        $this->data->merge($requestProperties->data->all());
        $this->config->merge($requestProperties->config->all());
        $this->guzzleMiddleware->merge($requestProperties->guzzleMiddleware->all());
        $this->responseInterceptors->merge($requestProperties->responseInterceptors->all());

        return $this;
    }
}
