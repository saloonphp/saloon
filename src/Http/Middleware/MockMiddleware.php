<?php

namespace Sammyjo20\Saloon\Http\Middleware;

use GuzzleHttp\Promise\FulfilledPromise;
use Psr\Http\Message\RequestInterface;
use Sammyjo20\Saloon\Http\MockResponse;

class MockMiddleware
{
    /**
     * @var MockResponse
     */
    protected MockResponse $mockResponse;

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
        return function (RequestInterface $request, array $options) use ($handler) {
            return new FulfilledPromise($this->mockResponse->toGuzzleResponse());
        };
    }
}
