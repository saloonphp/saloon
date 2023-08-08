<?php

declare(strict_types=1);

namespace Saloon\Traits\Plugins;

use Saloon\Enums\Timeout;
use Saloon\Contracts\PendingRequest;

trait HasTimeout
{
    /**
     * Boot HasTimeout plugin.
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
     */
    public function getConnectTimeout(): float
    {
        return property_exists($this, 'connectTimeout') ? $this->connectTimeout : Timeout::CONNECT->value;
    }

    /**
     * Get the request timeout.
     */
    public function getRequestTimeout(): float
    {
        return property_exists($this, 'requestTimeout') ? $this->requestTimeout : Timeout::REQUEST->value;
    }
}
