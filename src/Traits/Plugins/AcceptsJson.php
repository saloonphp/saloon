<?php declare(strict_types=1);

namespace Saloon\Traits\Plugins;

use Saloon\Http\PendingSaloonRequest;

trait AcceptsJson
{
    /**
     * @param PendingSaloonRequest $pendingRequest
     * @return void
     */
    public static function bootAcceptsJson(PendingSaloonRequest $pendingRequest): void
    {
        $pendingRequest->headers()->add('Accept', 'application/json');
    }
}
