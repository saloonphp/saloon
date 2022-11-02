<?php declare(strict_types=1);

namespace Sammyjo20\Saloon\Traits\Plugins;

use Sammyjo20\Saloon\Contracts\SaloonResponse;
use Sammyjo20\Saloon\Http\PendingSaloonRequest;

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
