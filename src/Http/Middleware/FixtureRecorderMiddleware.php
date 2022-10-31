<?php

namespace Sammyjo20\Saloon\Http\Middleware;

use Sammyjo20\Saloon\Contracts\SaloonResponse;
use Sammyjo20\Saloon\Http\Fixture;
use Psr\Http\Message\RequestInterface;
use Sammyjo20\Saloon\Data\FixtureData;
use Psr\Http\Message\ResponseInterface;

class FixtureRecorderMiddleware
{
    /**
     * The fixture
     *
     * @var Fixture
     */
    protected Fixture $fixture;

    /**
     * Constructor
     *
     * @param Fixture $fixture
     */
    public function __construct(Fixture $fixture)
    {
        $this->fixture = $fixture;
    }

    /**
     * Store the response
     *
     * @param SaloonResponse $response
     * @return void
     * @throws \JsonException
     * @throws \Sammyjo20\Saloon\Exceptions\UnableToCreateDirectoryException
     * @throws \Sammyjo20\Saloon\Exceptions\UnableToCreateFileException
     */
    public function __invoke(SaloonResponse $response): void
    {
        $fixtureData = FixtureData::fromResponse($response);

        $this->fixture->store($fixtureData);
    }
}
