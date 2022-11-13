<?php

namespace Saloon\Traits\Plugins;

use Saloon\Http\PendingRequest;

trait HasTimeout
{
    /**
     * Boot HasTimeout plugin.
     *
     * @param PendingRequest $pendingRequest
     * @return void
     */
    public function bootHasTimeout(PendingRequest $pendingRequest): void
    {
        $pendingRequest->config()->merge([
            'connect_timeout' => $this->getConnectTimeout(),
            'timeout' => $this->getRequestTimeout(),
        ]);
    }

    /**
     * Get the request connection timeout.
     *
     * @return float
     */
    public function getConnectTimeout(): float
    {
        return property_exists($this, 'connectTimeout') ? $this->connectTimeout : 10;
    }

    /**
     * Get the request timeout.
     *
     * @return float
     */
    public function getRequestTimeout(): float
    {
        return property_exists($this, 'requestTimeout') ? $this->requestTimeout : 30;
    }
}
