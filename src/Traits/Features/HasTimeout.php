<?php

namespace Sammyjo20\Saloon\Traits\Features;

trait HasTimeout
{
    /**
     * Define a timeout
     *
     * @var int
     */
    protected int $requestTimeout = 5;

    /**
     * @return void
     */
    public function bootHasTimeoutFeature()
    {
        if (isset($this->requestTimeout) && is_numeric($this->requestTimeout)) {
            $this->addConfig('timeout', ((float)$this->requestTimeout) / 1000);
        }
    }
}
