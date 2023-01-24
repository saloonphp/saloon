<?php

declare(strict_types=1);

namespace Saloon\Traits\Plugins;

use Saloon\Contracts\Response;
use Saloon\Contracts\PendingRequest;

trait AlwaysThrowOnErrors
{
    /**
     * Boot AlwaysThrowOnErrors Plugin
     *
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     * @return void
     */
    public static function bootAlwaysThrowOnErrors(PendingRequest $pendingRequest): void
    {
        $pendingRequest->middleware()->onResponse(fn (Response $response) => $response->throw());
    }
}
