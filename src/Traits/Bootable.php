<?php

declare(strict_types=1);

namespace Saloon\Traits;

use Saloon\Contracts\PendingRequest;

trait Bootable
{
    /**
     * Handle the boot lifecycle hook
     *
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     * @return void
     */
    public function boot(PendingRequest $pendingRequest): void
    {
        //
    }
}
