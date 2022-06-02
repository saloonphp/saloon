<?php

namespace Sammyjo20\Saloon\Traits\Plugins;

use Sammyjo20\Saloon\Http\PendingSaloonRequest;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\TestPipe;

trait AcceptsJson
{
    /**
     * @param PendingSaloonRequest $request
     * @return void
     */
    public function bootAcceptsJson(PendingSaloonRequest $request): void
    {
        $request->headers()->put('Accept', 'application/json');
    }
}
