<?php declare(strict_types=1);

namespace Saloon\Traits\Plugins;

use Saloon\Http\PendingSaloonRequest;

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
        $pendingRequest->config()->add('verify', false);
    }
}
