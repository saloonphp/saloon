<?php

namespace Sammyjo20\Saloon\Traits\Features;

trait HasTimeout
{
    public function bootHasTimeoutFeature()
    {
        if (isset($this->requestTimeout) && is_numeric($this->requestTimeout)) {
            $this->mergeConfig([
                'timeout' => ((float)$this->requestTimeout) / 1000,
            ]);
        }
    }
}
