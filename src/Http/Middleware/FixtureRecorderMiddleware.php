<?php

namespace Sammyjo20\Saloon\Http\Middleware;

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
     * @param callable $handler
     * @return \Closure
     */
    public function __invoke(callable $handler)
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            $promise = $handler($request, $options);

            return $promise->then(function (ResponseInterface $response) {
                $this->storeResponse($response);

                return $response;
            });
        };
    }

    /**
     * Store the response against the fixture.
     *
     * @param ResponseInterface $response
     * @return void
     * @throws \JsonException
     * @throws \Sammyjo20\Saloon\Exceptions\UnableToCreateDirectoryException
     * @throws \Sammyjo20\Saloon\Exceptions\UnableToCreateFileException
     */
    protected function storeResponse(ResponseInterface $response): void
    {
        $fixtureData = FixtureData::fromGuzzleResponse($response);

        $this->fixture->store($fixtureData);
    }
}
