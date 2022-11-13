<?php declare(strict_types=1);

namespace Saloon\Traits\Plugins;

use Saloon\Contracts\Response;
use Saloon\Http\PendingRequest;

trait AlwaysThrowsOnErrors
{
    /**
     * Always throw if there is something wrong with the request.
     *
     * @param PendingRequest $pendingRequest
     * @return void
     */
    public static function bootAlwaysThrowsOnErrors(PendingRequest $pendingRequest): void
    {
        $pendingRequest->middleware()->onResponse(fn (Response $response) => $response->throw());
    }
}
