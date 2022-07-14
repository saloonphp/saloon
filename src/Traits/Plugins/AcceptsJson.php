<?php

namespace Sammyjo20\Saloon\Traits\Plugins;

use Sammyjo20\Saloon\Http\PendingSaloonRequest;

trait AcceptsJson
{
    /**
     * @param PendingSaloonRequest $request
     * @return void
     */
    public static function bootAcceptsJson(PendingSaloonRequest $request): void
    {
        $request->headers()->add('Accept', 'application/json');
    }
}
