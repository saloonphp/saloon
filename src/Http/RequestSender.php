<?php

namespace Sammyjo20\Saloon\Http;

use Sammyjo20\Saloon\Interfaces\RequestSenderInterface;

abstract class RequestSender implements RequestSenderInterface
{
    public function processResponse(SaloonResponse $response): SaloonResponse
    {

    }
}
