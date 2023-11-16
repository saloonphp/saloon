<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use Saloon\Http\PendingRequest;

interface HasFakeData
{
    /**
     * Get the fake data for the mocked request.
     */
    public function getFakeData(PendingRequest $request): mixed;
}
