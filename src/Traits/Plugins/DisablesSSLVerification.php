<?php

namespace Sammyjo20\Saloon\Traits\Plugins;

use Sammyjo20\Saloon\Http\PendingSaloonRequest;
use Sammyjo20\Saloon\Http\SaloonRequest;

trait DisablesSSLVerification
{
    /**
     * Disable SSL verification on requests. I hope you know this is bad.
     *
     * @param PendingSaloonRequest $request
     * @return void
     */
    public static function bootDisablesSSLVerification(PendingSaloonRequest $request): void
    {
        $request->config()->push('verify', false);
    }
}
