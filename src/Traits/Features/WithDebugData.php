<?php

namespace Sammyjo20\Saloon\Traits\Features;

trait WithDebugData
{
    /**
     * Enable debug mode
     *
     * @return void
     */
    public function bootWithDebugData()
    {
        $this->addConfig('debug', true);
    }
}
