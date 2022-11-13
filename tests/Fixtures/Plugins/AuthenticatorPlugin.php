<?php declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Plugins;

use Saloon\Http\PendingSaloonRequest;

trait AuthenticatorPlugin
{
    /**
     * @param PendingSaloonRequest $pendingRequest
     * @return void
     */
    public function bootAuthenticatorPlugin(PendingSaloonRequest $pendingRequest): void
    {
        $pendingRequest->withTokenAuth('plugin-auth');
    }
}
