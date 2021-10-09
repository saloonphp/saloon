<?php

namespace Sammyjo20\Saloon\Traits;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;

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

    private function createMockHandler(): MockHandler
    {
        $mockData = $this->mockType === 'success'
            ? $this->request->mockSuccessResponse()
            : $this->request->mockFailureResponse();

        if (isset($mockData['body']) && is_array($mockData['body'])) {
            $mockData['body'] = json_encode($mockData['body']);
        }

        $mockHandler = new MockHandler([
            new Response($mockData['status'], $mockData['headers'], $mockData['body'])
        ]);

        return $mockHandler;
    }
}
