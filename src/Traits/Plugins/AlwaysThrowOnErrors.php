<?php

declare(strict_types=1);

namespace Saloon\Traits\Plugins;

use Saloon\Http\Response;
use Saloon\Enums\PipeOrder;
use Saloon\Http\PendingRequest;

trait AlwaysThrowOnErrors
{
    /**
     * Boot AlwaysThrowOnErrors Plugin
     */
    public static function bootAlwaysThrowOnErrors(PendingRequest $pendingRequest): void
    {
        // This middleware will simply use the "throw" method on the response
        // which will check if the connector/request deems the response as a
        // failure - if it does, it will throw a RequestException.

        $pendingRequest->middleware()->onResponse(
            callable: static fn (Response $response) => $response->throw(),
            name: 'alwaysThrowOnErrors',
            order: PipeOrder::LAST
        );
    }
}
