<?php

namespace Sammyjo20\Saloon\Http\Guzzle\Middleware;

use GuzzleHttp\Psr7\Response;
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

        return fn () => new FulfilledPromise($this->createGuzzleResponse());
    }

    /**
     * Create a Guzzle Response.
     *
     * @return Response
     * @throws \JsonException
     */
    protected function createGuzzleResponse(): Response
    {
        $mockResponse = $this->mockResponse;

        $status = $mockResponse->getStatus();
        $headers = $mockResponse->getHeaders();
        $data = $mockResponse->getData();

        $formattedData = $data->isArray() ? json_encode($data->all(), JSON_THROW_ON_ERROR) : $data->all();

        return new Response($status, $headers->all(), $formattedData);
    }
}
