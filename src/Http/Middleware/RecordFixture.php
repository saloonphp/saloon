<?php declare(strict_types=1);

namespace Saloon\Http\Middleware;

use Saloon\Data\FixtureData;
use Saloon\Http\Faking\Fixture;
use Saloon\Contracts\SaloonResponse;
use Saloon\Contracts\ResponseMiddleware;

class RecordFixture implements ResponseMiddleware
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
