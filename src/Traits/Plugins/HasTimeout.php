<?php

namespace Sammyjo20\Saloon\Traits\Plugins;

trait HasTimeout
{
    /**
     * Register the timeout on the resource using the plugin.
     *
     * @return void
     */
    public function bootHasTimeout(): void
    {
        $this->addConfig('connect_timeout', $this->getConnectTimeout());
        $this->addConfig('timeout', $this->getRequestTimeout());
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
