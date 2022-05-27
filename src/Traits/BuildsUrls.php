<?php

namespace Sammyjo20\Saloon\Traits;

trait BuildsUrls
{
    /**
     * Build up the full request URL.
     *
     * @return string
     */
    public function getRequestUrl(): string
    {
        $requestEndpoint = $this->defineEndpoint();

        if ($requestEndpoint !== '/') {
            $requestEndpoint = ltrim($requestEndpoint, '/ ');
        }

        $requiresTrailingSlash = ! empty($requestEndpoint) && $requestEndpoint !== '/';

        $baseEndpoint = rtrim($this->getConnector()->defineBaseUrl(), '/ ');
        $baseEndpoint = $requiresTrailingSlash ? $baseEndpoint . '/' : $baseEndpoint;

        return $baseEndpoint . $requestEndpoint;
    }
}
