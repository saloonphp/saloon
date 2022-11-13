<?php declare(strict_types=1);

namespace Saloon\Traits\Request;

use Saloon\Exceptions\SaloonInvalidConnectorException;

trait BuildsUrls
{
    /**
     * Build up the full request URL.
     *
     * @return string
     * @throws SaloonInvalidConnectorException
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
