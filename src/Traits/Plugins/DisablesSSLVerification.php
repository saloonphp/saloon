<?php

namespace Sammyjo20\Saloon\Traits\Plugins;

use Sammyjo20\Saloon\Http\PendingSaloonRequest;

trait DisablesSSLVerification
{
    /**
     * Disable SSL verification on requests. I hope you know this is bad.
     *
     * @param PendingSaloonRequest $pendingRequest
     * @return void
     */
    public static function bootDisablesSSLVerification(PendingSaloonRequest $pendingRequest): void
    {
        $request->config()->add('verify', false);
    }
}
