<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Plugins;

use Saloon\Http\PendingRequest;

trait AuthenticatorPlugin
{
    public function bootAuthenticatorPlugin(PendingRequest $pendingRequest): void
    {
        $pendingRequest->withTokenAuth('plugin-auth');
    }
}
