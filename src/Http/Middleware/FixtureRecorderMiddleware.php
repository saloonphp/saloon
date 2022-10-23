<?php

namespace Sammyjo20\Saloon\Http\Middleware;

use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Promise\RejectedPromise;
use Psr\Http\Message\ResponseInterface;
use Sammyjo20\Saloon\Http\Fixture;
use Sammyjo20\Saloon\Http\MockResponse;
use GuzzleHttp\Promise\FulfilledPromise;
use Sammyjo20\Saloon\Tests\Fixtures\Data\FixtureData;

class FixtureRecorderMiddleware
{
    /**
     * @var Fixture
     */
    protected Fixture $fixture;

    /**
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

    protected function storeResponse(ResponseInterface $response): void
    {
        $fixtureData = FixtureData::fromGuzzleResponse($response);

        $this->fixture->store($fixtureData);
    }
}
