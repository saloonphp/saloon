<?php

namespace Sammyjo20\Saloon\Traits;

use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Http\SaloonResponse;

trait InterceptsRequests
{
    public function interceptRequest(SaloonRequest $request): SaloonRequest
    {
        // This will give you an instance of Http or Guzzle to allow
        // you to make any changes before the request goes out.

        return $request;
    }

    public function interceptResponse(SaloonRequest $request, SaloonResponse $response): SaloonResponse
    {
        // This will be called before anything else (including success/error handling)
        // You can do anything like logging etc.

        return $response;
    }
}
