<?php

declare(strict_types=1);

namespace Saloon\Traits\Request;

use Saloon\Exceptions\InvalidConnectorException;

trait BuildsUrls
{
    /**
     * Build up the full request URL.
     *
     * @return string
     * @throws InvalidConnectorException
     */
    public function getRequestUrl(): string
    {
        $requestEndpoint = $this->defineEndpoint();

        if ($requestEndpoint !== '/') {
            $requestEndpoint = ltrim($requestEndpoint, '/ ');
        }

        $requiresTrailingSlash = ! empty($requestEndpoint) && $requestEndpoint !== '/';

        $baseEndpoint = rtrim($this->connector()->defineBaseUrl(), '/ ');
        $baseEndpoint = $requiresTrailingSlash ? $baseEndpoint . '/' : $baseEndpoint;

        return $baseEndpoint . $requestEndpoint;
    }
}
