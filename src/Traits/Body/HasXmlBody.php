<?php

declare(strict_types=1);

namespace Saloon\Traits\Body;

use Saloon\Http\PendingRequest;

trait HasXmlBody
{
    use HasStringBody;

    /**
     * Boot the plugin
     */
    public function bootHasXmlBody(PendingRequest $pendingRequest): void
    {
        $pendingRequest->headers()->add('Content-Type', 'application/xml');
    }
}
