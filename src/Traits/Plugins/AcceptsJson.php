<?php

namespace Sammyjo20\Saloon\Traits\Plugins;

use Sammyjo20\Saloon\Http\PendingSaloonRequest;

trait AcceptsJson
{
    /**
     * @param PendingSaloonRequest $request
     * @return void
     */
    public function bootAcceptsJson(PendingSaloonRequest $request): void
    {
        $request->headers()->push('Accept', 'application/json');
    }
}
