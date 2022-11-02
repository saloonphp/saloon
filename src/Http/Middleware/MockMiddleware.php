<?php declare(strict_types=1);

namespace Sammyjo20\Saloon\Http\Middleware;

use Sammyjo20\Saloon\Http\Fixture;
use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Http\PendingSaloonRequest;
use Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException;
use Sammyjo20\Saloon\Exceptions\SaloonNoMockResponseFoundException;

class MockMiddleware
{
    /**
     * Guess a mock response
     *
     * @param PendingSaloonRequest $pendingRequest
     * @return PendingSaloonRequest|MockResponse
     * @throws SaloonInvalidConnectorException
     * @throws SaloonNoMockResponseFoundException
     * @throws \JsonException
     * @throws \Sammyjo20\Saloon\Exceptions\FixtureMissingException
     */
    public function __invoke(PendingSaloonRequest $pendingRequest): PendingSaloonRequest|MockResponse
    {
        if ($pendingRequest->hasMockClient() === false) {
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
            $pendingRequest->middleware()->onResponse(new FixtureRecorderMiddleware($mockObject));
        }

        return $pendingRequest;
    }
}
