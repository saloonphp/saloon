<?php

declare(strict_types=1);

namespace Saloon\Http\Middleware;

use Saloon\Contracts\Response;
use Saloon\Http\Faking\Fixture;
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
     * Constructor
     *
     * @param \Saloon\Http\Faking\Fixture $fixture
     */
    public function __construct(Fixture $fixture)
    {
        $this->fixture = $fixture;
    }

    /**
     * Store the response
     *
     * @param \Saloon\Contracts\Response $response
     * @return void
     * @throws \JsonException
     * @throws \Saloon\Exceptions\UnableToCreateDirectoryException
     * @throws \Saloon\Exceptions\UnableToCreateFileException
     */
    public function __invoke(Response $response): void
    {
        $this->fixture->store(
            ResponseRecorder::record($response)
        );
    }
}
