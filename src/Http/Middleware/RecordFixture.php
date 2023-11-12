<?php

declare(strict_types=1);

namespace Saloon\Http\Middleware;

use Saloon\Contracts\Response;
use Saloon\Http\Faking\Fixture;
use Saloon\Contracts\MockClient;
use Saloon\Helpers\ResponseRecorder;
use Saloon\Contracts\ResponseMiddleware;

class RecordFixture implements ResponseMiddleware
{
    /**
     * The Fixture
     *
     * @var \Saloon\Http\Faking\Fixture
     */
    protected Fixture $fixture;

    /**
     * Mock Client
     */
    protected MockClient $mockClient;

    /**
     * Constructor
     *
     * @param \Saloon\Http\Faking\Fixture $fixture
     * @param \Saloon\Contracts\MockClient $mockClient
     */
    public function __construct(Fixture $fixture, MockClient $mockClient)
    {
        $this->fixture = $fixture;
        $this->mockClient = $mockClient;
    }

    /**
     * Store the response
     *
     * @param \Saloon\Contracts\Response $response
     * @return void
     * @throws \JsonException
     * @throws \Saloon\Exceptions\FixtureException
     * @throws \Saloon\Exceptions\UnableToCreateDirectoryException
     * @throws \Saloon\Exceptions\UnableToCreateFileException
     */
    public function __invoke(Response $response): void
    {
        $this->fixture->store(
            ResponseRecorder::record($response)
        );

        $this->mockClient->recordResponse($response);
    }
}
