<?php declare(strict_types=1);

namespace Saloon\Traits\Plugins;

use Saloon\Contracts\PendingRequest;

trait DisablesSSLVerification
{
    /**
     * Disable SSL verification on requests. I hope you know this is bad.
     *
     * @param PendingRequest $pendingRequest
     * @return void
     */
    public static function bootDisablesSSLVerification(PendingRequest $pendingRequest): void
    {
        $pendingRequest->config()->add('verify', false);
    }
}
