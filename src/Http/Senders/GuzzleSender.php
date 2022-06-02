<?php

namespace Sammyjo20\Saloon\Http\Senders;

use Sammyjo20\Saloon\Http\PendingSaloonRequest;
use Sammyjo20\Saloon\Http\SaloonResponse;
use Sammyjo20\Saloon\Interfaces\RequestSenderInterface;

class GuzzleSender implements RequestSenderInterface
{
    public function handle(PendingSaloonRequest $request): SaloonResponse
    {
        dd('Send request...', $request->headers());

        // TODO: Implement handle() method.
        // 1. Create a guzzle client
        // 2. Spin up the guzzle middleware
        // 3. Create a request
        // 4. Send request
        // 5. Process response interceptors
    }
}
