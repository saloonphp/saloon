<?php declare(strict_types=1);

namespace Saloon\Traits\Plugins;

use Saloon\Contracts\SaloonResponse;
use Saloon\Http\PendingSaloonRequest;

trait AlwaysThrowsOnErrors
{
    /**
     * Always throw if there is something wrong with the request.
     *
     * @param PendingSaloonRequest $pendingRequest
     * @return void
     */
    public static function bootAlwaysThrowsOnErrors(PendingSaloonRequest $pendingRequest): void
    {
        $pendingRequest->middleware()->onResponse(fn (SaloonResponse $response) => $response->throw());
    }
}
