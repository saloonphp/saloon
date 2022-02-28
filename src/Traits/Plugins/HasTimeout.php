<?php

namespace Sammyjo20\Saloon\Traits\Plugins;

use Sammyjo20\Saloon\Http\SaloonRequest;

trait HasTimeout
{
    /**
     * Register the timeout on the resource using the plugin.
     *
     * @param SaloonRequest $request
     * @return void
     */
    public function bootHasTimeout(SaloonRequest $request): void
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
