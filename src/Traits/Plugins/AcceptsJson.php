<?php declare(strict_types=1);

namespace Sammyjo20\Saloon\Traits\Plugins;

use Sammyjo20\Saloon\Http\PendingSaloonRequest;

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
