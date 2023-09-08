<?php

declare(strict_types=1);

namespace Saloon\Traits\Plugins;

use Saloon\Http\PendingRequest;

trait AcceptsJson
{
    /**
     * Boot AcceptsJson Plugin
     */
    public static function bootAcceptsJson(PendingRequest $pendingRequest): void
    {
        $pendingRequest->headers()->add('Accept', 'application/json');
    }
}
