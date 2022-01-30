<?php

namespace Sammyjo20\Saloon\Http\Middleware;

use Sammyjo20\Saloon\Http\MockResponse;
use GuzzleHttp\Promise\FulfilledPromise;

class MockMiddleware
{
    /**
     * @var MockResponse
     */
    protected MockResponse $mockResponse;

    /**
     * @param MockResponse $mockResponse
     */
    public function __construct(MockResponse $mockResponse)
    {
        $this->mockResponse = $mockResponse;
    }

    /**
     * Return the fake fulfilled response.
     *
     * @param callable $handler
     * @return \Closure
     */
    public function __invoke(callable $handler)
    {
        return function () {
            return new FulfilledPromise($this->mockResponse->toGuzzleResponse());
        };
    }
}
