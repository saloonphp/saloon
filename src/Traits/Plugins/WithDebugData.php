<?php

namespace Sammyjo20\Saloon\Traits\Plugins;

trait WithDebugData
{
    /**
     * Enable debug mode.
     *
     * @return void
     */
    public function bootWithDebugData(): void
    {
        $this->addConfig('debug', true);
    }
}
