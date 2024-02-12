<?php

declare(strict_types=1);

namespace Saloon\Traits\Plugins;

use Saloon\Config;
use GuzzleHttp\RequestOptions;
use Saloon\Http\PendingRequest;

trait HasTimeout
{
    /**
     * Boot HasTimeout plugin.
     */
    public function bootHasTimeout(PendingRequest $pendingRequest): void
    {
        $pendingRequest->config()->merge([
            RequestOptions::CONNECT_TIMEOUT => $this->getConnectTimeout(),
            RequestOptions::TIMEOUT => $this->getRequestTimeout(),
        ]);
    }

    /**
     * Get the request connection timeout.
     */
    public function getConnectTimeout(): float
    {
        return property_exists($this, 'connectTimeout') ? $this->connectTimeout : Config::$defaultConnectionTimeout;
    }

    /**
     * Get the request timeout.
     */
    public function getRequestTimeout(): float
    {
        return property_exists($this, 'requestTimeout') ? $this->requestTimeout : Config::$defaultRequestTimeout;
    }
}
