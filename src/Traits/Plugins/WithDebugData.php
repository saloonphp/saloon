<?php

declare(strict_types=1);

namespace Saloon\Traits\Plugins;

use Saloon\Contracts\PendingRequest;

trait WithDebugData
{
    /**
     * Enable debug mode.
     *
     * @param PendingRequest $pendingRequest
     * @return void
     */
    public static function bootWithDebugData(PendingRequest $pendingRequest): void
    {
        $pendingRequest->config()->add('debug', true);
    }
}
