<?php

declare(strict_types=1);

namespace Saloon\Http\Middleware;

use Saloon\Http\Response;
use Saloon\Http\Faking\Fixture;
use Saloon\Data\RecordedResponse;
use Saloon\Contracts\ResponseMiddleware;

class RecordFixture implements ResponseMiddleware
{
    /**
     * The Fixture
     */
    protected Fixture $fixture;

    /**
     * Constructor
     */
    public function __construct(Fixture $fixture)
    {
        $this->fixture = $fixture;
    }

    /**
     * Store the response
     *
     * @throws \JsonException
     * @throws \Saloon\Exceptions\FixtureException
     * @throws \Saloon\Exceptions\UnableToCreateDirectoryException
     * @throws \Saloon\Exceptions\UnableToCreateFileException
     */
    public function __invoke(Response $response): void
    {
        $this->fixture->store(
            RecordedResponse::fromResponse($response)
        );
    }
}
