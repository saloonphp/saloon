<?php declare(strict_types=1);

namespace Saloon\Traits\Plugins;

use Saloon\Http\PendingSaloonRequest;

trait WithDebugData
{
    /**
     * Enable debug mode.
     *
     * @param PendingSaloonRequest $pendingRequest
     * @return void
     */
    public static function bootWithDebugData(PendingSaloonRequest $pendingRequest): void
    {
        $pendingRequest->config()->add('debug', true);
    }
}
