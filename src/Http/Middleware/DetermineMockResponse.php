<?php

declare(strict_types=1);

namespace Saloon\Http\Middleware;

use Saloon\Http\Faking\Fixture;
use Saloon\Contracts\PendingRequest;
use Saloon\Http\Faking\MockResponse;
use Saloon\Contracts\RequestMiddleware;

class DetermineMockResponse implements RequestMiddleware
{
    /**
     * Guess a mock response
     *
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     * @return \Saloon\Contracts\PendingRequest|MockResponse
     * @throws \JsonException
     * @throws \Saloon\Exceptions\FixtureMissingException
     */
    public function __invoke(PendingRequest $pendingRequest): PendingRequest|MockResponse
    {
        if ($pendingRequest->hasMockClient() === false) {
            return $pendingRequest;
        }

        if ($pendingRequest->hasSimulatedResponsePayload()) {
            return $pendingRequest;
        }

        $mockClient = $pendingRequest->getMockClient();

        // When we guess the next response from the MockClient it will
        // either return a MockResponse instance or a Fixture instance.

        $mockObject = $mockClient->guessNextResponse($pendingRequest);

        $mockResponse = $mockObject instanceof Fixture ? $mockObject->getMockResponse() : $mockObject;

        // If the mock response is a valid instance, we will return it.
        // The middleware pipeline will recognise this and will set
        // it as the "SimulatedResponse" on the request.

        if ($mockResponse instanceof MockResponse) {
            return $mockResponse;
        }

        // However if the mock response is not valid because it is
        // an instance of a fixture instead, we will register a
        // middleware on the response to record the response.

        if (is_null($mockResponse) && $mockObject instanceof Fixture) {
            $pendingRequest->middleware()->onResponse(new RecordFixture($mockObject), true, 'recordFixture');
        }

        return $pendingRequest;
    }
}
