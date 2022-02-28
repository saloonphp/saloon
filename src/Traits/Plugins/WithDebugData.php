<?php

namespace Sammyjo20\Saloon\Traits\Plugins;

use Sammyjo20\Saloon\Http\SaloonRequest;

trait WithDebugData
{
    /**
     * Enable debug mode.
     *
     * @param SaloonRequest $request
     * @return void
     */
    public function bootWithDebugData(SaloonRequest $request): void
    {
        $this->addConfig('debug', true);
    }
}
