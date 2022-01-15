<?php

namespace Sammyjo20\Saloon\Traits;

use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Client as GuzzleClient;

trait ManagesGuzzle
{
    private GuzzleClient $guzzleClient;

    private function createGuzzleClient(): void
    {
        $clientConfig = [
            'base_uri' => rtrim($this->connector->defineBaseUrl(), '/ ') . '/',
        ];

        if ($this->isMocking === true) {
            $clientConfig['handler'] = HandlerStack::create($this->createMockHandler());
        }

        $this->guzzleClient = new GuzzleClient($clientConfig);
    }

    /**
     * Create a "mock" handler so Guzzle can pretend it's a real request.
     *
     * @return MockHandler
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonMissingMockException
     */
    private function createMockHandler(): MockHandler
    {
        $saloonMock = $this->mockType === 'success'
            ? $this->request->getSuccessMock()
            : $this->request->getFailureMock();

        return new MockHandler([
            new Response($saloonMock->getStatusCode(), $saloonMock->getHeaders(), $saloonMock->getBody()),
        ]);
    }
}
