<?php

namespace Sammyjo20\Saloon\Traits\Plugins;

use Sammyjo20\Saloon\Http\Middleware\ThrowPipe;
use Sammyjo20\Saloon\Http\PendingSaloonRequest;

trait AlwaysThrowsOnErrors
{
    /**
     * Always throw if there is something wrong with the request.
     *
     * @param PendingSaloonRequest $request
     * @return void
     */
    public static function bootAlwaysThrowsOnErrors(PendingSaloonRequest $request): void
    {
        $request->middlewarePipeline()
            ->addResponsePipe(new ThrowPipe);
    }
}
