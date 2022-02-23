<?php

namespace Sammyjo20\Saloon\Traits\Features;

trait HasTimeout
{
    /**
     * Register the timeout on the resource using the plugin.
     *
     * @return void
     */
    public function bootHasTimeout()
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
        return $this->connectTimeout ?? 30;
    }

    /**
     * Get the request timeout.
     *
     * @return float
     */
    public function getRequestTimeout(): float
    {
        return $this->requestTimeout ?? 30;
    }
}
