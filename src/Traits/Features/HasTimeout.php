<?php

namespace Sammyjo20\Saloon\Traits\Features;

trait HasTimeout
{
    /**
     * Define a default connection timeout in seconds.
     *
     * @var int
     */
    protected int $connectTimeout = 30;

    /**
     * Define a default timeout in seconds.
     *
     * @var int
     */
    protected int $requestTimeout = 30;

    /**
     * Register the timeout on the resource using the plugin.
     *
     * @return void
     */
    public function bootHasTimeoutFeature()
    {
        $this->addConfig('connect_timeout', $this->getConnectTimeout());
        $this->addConfig('timeout', $this->getRequestTimeout());
    }

    /**
     * Get the request connection timeout.
     *
     * @return int
     */
    public function getConnectTimeout(): int
    {
        return $this->connectTimeout;
    }

    /**
     * Get the request timeout.
     *
     * @return int
     */
    public function getRequestTimeout(): int
    {
        return $this->requestTimeout;
    }
}
