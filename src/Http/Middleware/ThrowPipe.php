<?php

namespace Sammyjo20\Saloon\Http\Middleware;

use Sammyjo20\Saloon\Http\Responses\SaloonResponse;

class ThrowPipe
{
    /**
     * Throw if an error happens.
     *
     * @param SaloonResponse $response
     * @return void
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonRequestException
     */
    public function __invoke(SaloonResponse $response): void
    {
        $response->throw();
    }
}
