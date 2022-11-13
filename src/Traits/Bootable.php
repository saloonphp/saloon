<?php declare(strict_types=1);

namespace Saloon\Traits;

use Saloon\Http\PendingSaloonRequest;

trait Bootable
{
    /**
     * Handle the boot lifecycle hook
     *
     * @param PendingSaloonRequest $pendingRequest
     * @return void
     */
    public function boot(PendingSaloonRequest $pendingRequest): void
    {
        //
    }
}
