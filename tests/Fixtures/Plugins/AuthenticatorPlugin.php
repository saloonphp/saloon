<?php declare(strict_types=1);

namespace Sammyjo20\Saloon\Tests\Fixtures\Plugins;

use Sammyjo20\Saloon\Http\PendingSaloonRequest;

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
