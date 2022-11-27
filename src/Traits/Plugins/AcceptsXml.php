<?php

declare(strict_types=1);

namespace Saloon\Traits\Plugins;

use Saloon\Contracts\PendingRequest;

trait AcceptsXml
{
    /**
     * Boot the AcceptsXml trait.
     *
     * @param PendingRequest $pendingRequest
     * @return void
     */
    public static function bootAcceptsXml(PendingRequest $pendingRequest): void
    {
        $pendingRequest->headers()->add('Accept', 'application/xml');
    }
}
