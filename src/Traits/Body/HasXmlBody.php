<?php

declare(strict_types=1);

namespace Saloon\Traits\Body;

use Saloon\Contracts\PendingRequest;

trait HasXmlBody
{
    use HasBody;

    /**
     * Boot the plugin
     *
     * @param PendingRequest $pendingRequest
     * @return void
     */
    public function bootHasXmlBody(PendingRequest $pendingRequest): void
    {
        $pendingRequest->headers()->add('Accept', 'application/xml');
        $pendingRequest->headers()->add('Content-Type', 'application/xml');
    }
}
