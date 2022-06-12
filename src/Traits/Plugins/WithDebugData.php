<?php

namespace Sammyjo20\Saloon\Traits\Plugins;

use Sammyjo20\Saloon\Http\PendingSaloonRequest;

trait WithDebugData
{
    /**
     * Enable debug mode.
     *
     * @param PendingSaloonRequest $request
     * @return void
     */
    public static function bootWithDebugData(PendingSaloonRequest $request): void
    {
        $request->config()->push('debug', true);
    }
}
