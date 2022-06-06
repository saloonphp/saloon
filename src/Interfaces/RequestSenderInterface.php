<?php

namespace Sammyjo20\Saloon\Interfaces;

use Sammyjo20\Saloon\Http\SaloonResponse;
use Sammyjo20\Saloon\Http\PendingSaloonRequest;

interface RequestSenderInterface
{
    /**
     * Send the request.
     *
     * @param PendingSaloonRequest $request
     * @return SaloonResponse
     */
    public function handle(PendingSaloonRequest $request): SaloonResponse;

    /**
     * Process the response
     *
     * @param SaloonResponse $response
     * @return SaloonResponse
     */
    public function processResponse(SaloonResponse $response): SaloonResponse;
}
