<?php

namespace Sammyjo20\Saloon\Http\Middleware;

use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Promise\RejectedPromise;
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
        $mockResponse = $this->mockResponse;

        if ($mockResponse->throwsException()) {
            return fn (RequestInterface $request) => new RejectedPromise($mockResponse->getException($request));
        }

        return fn () => new FulfilledPromise($this->mockResponse->toGuzzleResponse());
    }
}
