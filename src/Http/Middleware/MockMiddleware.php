<?php

namespace Sammyjo20\Saloon\Http\Middleware;

class MockMiddleware
{
    public function __invoke(callable $handler)
    {
        dd($handler);
    }
}
