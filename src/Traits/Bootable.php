<?php

declare(strict_types=1);

namespace Saloon\Traits;

use Saloon\Http\PendingRequest;

trait Bootable
{
    /**
     * Handle the boot lifecycle hook
     */
    public function boot(PendingRequest $pendingRequest): void
    {
        //
    }
}
