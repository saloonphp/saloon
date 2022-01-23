<?php

namespace Sammyjo20\Saloon\Traits\Features;

trait WithDebugData
{
    /**
     * Enable debug mode
     *
     * @return void
     */
    public function bootWithDebugDataFeature()
    {
        $this->addConfig('debug', true);
    }
}
