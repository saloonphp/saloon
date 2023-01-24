<?php

declare(strict_types=1);

namespace Saloon\Traits\Plugins;

use Saloon\Contracts\PendingRequest;

trait AcceptsJson
{
    /**
     * Boot AcceptsJson Plugin
     *
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     * @return void
     */
    public static function bootAcceptsJson(PendingRequest $pendingRequest): void
    {
        $pendingRequest->headers()->add('Accept', 'application/json');
    }
}
