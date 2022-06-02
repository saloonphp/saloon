<?php

namespace Sammyjo20\Saloon\Http\Middleware;

use Sammyjo20\Saloon\Http\SaloonResponse;

class DataObjectPipe
{
    public function __invoke(SaloonResponse $response): void
    {
        dd('Set DTO', $response);
    }
}
