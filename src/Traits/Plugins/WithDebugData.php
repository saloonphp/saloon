<?php declare(strict_types=1);

namespace Sammyjo20\Saloon\Traits\Plugins;

use Sammyjo20\Saloon\Http\PendingSaloonRequest;

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
        $request->config()->add('debug', true);
    }
}
