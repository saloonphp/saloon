<?php

namespace Sammyjo20\Saloon\Tests\Fixtures\Mocking;

use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\Saloon\Http\SaloonRequest;

class CallableMockResponse
{
    public function __invoke(SaloonRequest $request): MockResponse
    {
        return new MockResponse(['request_class' => get_class($request)]);
    }
}
