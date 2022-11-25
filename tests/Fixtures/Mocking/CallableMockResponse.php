<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Mocking;

use Saloon\Http\PendingRequest;
use Saloon\Http\Faking\MockResponse;

class CallableMockResponse
{
    public function __invoke(PendingRequest $pendingRequest): MockResponse
    {
        return new MockResponse(['request_class' => get_class($pendingRequest->getRequest())], 200);
    }
}
