<?php declare(strict_types=1);

namespace Sammyjo20\Saloon\Tests\Fixtures\Mocking;

use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\Saloon\Http\PendingSaloonRequest;
use Sammyjo20\Saloon\Http\SaloonRequest;

class CallableMockResponse
{
    public function __invoke(PendingSaloonRequest $pendingRequest): MockResponse
    {
        return new MockResponse(200, ['request_class' => get_class($pendingRequest->getRequest())]);
    }
}
