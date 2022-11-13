<?php declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Mocking;

use Saloon\Http\Faking\MockResponse;
use Saloon\Http\PendingSaloonRequest;

class CallableMockResponse
{
    public function __invoke(PendingSaloonRequest $pendingRequest): MockResponse
    {
        return new MockResponse(200, ['request_class' => get_class($pendingRequest->getRequest())]);
    }
}
