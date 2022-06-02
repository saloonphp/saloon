<?php

namespace Sammyjo20\Saloon\Interfaces;

use Sammyjo20\Saloon\Http\PendingSaloonRequest;
use Sammyjo20\Saloon\Http\SaloonResponse;

interface RequestSenderInterface
{
    /**
     * Send the request.
     *
     * @param PendingSaloonRequest $request
     * @return SaloonResponse
     */
    public function handle(PendingSaloonRequest $request): SaloonResponse;
}
