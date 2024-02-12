<?php

declare(strict_types=1);

namespace Saloon\Http\Middleware;

use Saloon\Enums\PipeOrder;
use Saloon\Http\Faking\Fixture;
use Saloon\Http\PendingRequest;
use Saloon\Http\Faking\MockResponse;
use Saloon\Contracts\RequestMiddleware;

class DetermineMockResponse implements RequestMiddleware
{
    /**
     * Check if a MockClient has been provided and guess the MockResponse based on the request.
     *
     * @throws \Saloon\Exceptions\FixtureMissingException
     * @throws \Saloon\Exceptions\NoMockResponseFoundException
     */
    public function __invoke(PendingRequest $pendingRequest): PendingRequest
    {
        if ($pendingRequest->hasMockClient() === false) {
            return $pendingRequest;
        }

        if ($pendingRequest->hasFakeResponse()) {
            return $pendingRequest;
        }

        $mockClient = $pendingRequest->getMockClient();

        // When we guess the next response from the MockClient it will
        // either return a MockResponse instance or a Fixture instance.

        $mockObject = $mockClient->guessNextResponse($pendingRequest);

        $mockResponse = $mockObject instanceof Fixture ? $mockObject->getMockResponse() : $mockObject;

        // If the mock response is a valid instance, we will return it.
        // The middleware pipeline will recognise this and will set
        // it as the "FakeResponse" on the PendingRequest.

        if ($mockResponse instanceof MockResponse) {
            return $pendingRequest->setFakeResponse($mockResponse);
        }

        // However if the mock response is not valid because it is
        // an instance of a fixture instead, we will register a
        // middleware on the response to record the response.

        if (is_null($mockResponse) && $mockObject instanceof Fixture) {
            $pendingRequest->middleware()->onResponse(new RecordFixture($mockObject, $mockClient), 'recordFixture', PipeOrder::FIRST);
        }

        return $pendingRequest;
    }
}
