<?php

namespace Sammyjo20\Saloon\Traits;

use GuzzleHttp\Psr7\Request;
use Sammyjo20\Saloon\Http\SaloonResponse;

trait InterceptsGuzzle
{
    public function interceptRequest(Request $requestInstance): Request
    {
        // This will give you an instance of Http or Guzzle to allow
        // you to make any changes before the request goes out.

        return $requestInstance;
    }

    public function interceptResponse($requestInstance, SaloonResponse $responseInstance): SaloonResponse
    {
        // This will be called before anything else (including success/error handling)
        // You can do anything like logging etc.

        return $responseInstance;
    }
}
