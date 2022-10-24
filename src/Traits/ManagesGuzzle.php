<?php

namespace Sammyjo20\Saloon\Traits;

use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use Sammyjo20\Saloon\Http\Fixture;
use GuzzleHttp\Client as GuzzleClient;
use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\Saloon\Http\Middleware\MockMiddleware;
use Sammyjo20\Saloon\Exceptions\SaloonInvalidHandlerException;
use Sammyjo20\Saloon\Http\Middleware\FixtureRecorderMiddleware;
use Sammyjo20\Saloon\Exceptions\SaloonDuplicateHandlerException;

trait ManagesGuzzle
{
    /**
     * The list of booted handlers
     *
     * @var array
     */
    private array $bootedHandlers = [];

    /**
     * Create the Guzzle request
     *
     * @return Request
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     */
    public function createGuzzleRequest(): Request
    {
        return new Request($this->request->getMethod(), $this->request->getFullRequestUrl());
    }

    /**
     * Create a new Guzzle client
     *
     * @return GuzzleClient
     * @throws SaloonDuplicateHandlerException
     * @throws SaloonInvalidHandlerException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonMissingMockException
     */
    private function createGuzzleClient(): GuzzleClient
    {
        $clientConfig = [
            'connect_timeout' => 10,
            'timeout' => 30,
            'http_errors' => true,
        ];

        $clientConfig['handler'] = $this->bootHandlers(HandlerStack::create());

        return new GuzzleClient($clientConfig);
    }

    /**
     * Boot each of the handlers
     *
     * @param HandlerStack $handlerStack
     * @return HandlerStack
     * @throws SaloonDuplicateHandlerException
     * @throws \ReflectionException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonNoMockResponseFoundException
     */
    private function bootHandlers(HandlerStack $handlerStack): HandlerStack
    {
        foreach ($this->getHandlers() as $handler => $handlerClosure) {
            if (empty($handler) || empty($handlerClosure)) {
                continue;
            }

            // Let's make sure the handler isn't already added to the list of handlers
            // if it is - this is bad, so we should throw an exception.

            if (in_array($handler, $this->bootedHandlers, false)) {
                throw new SaloonDuplicateHandlerException($handler);
            }

            // Once that's all good, push the handler onto the stack.

            $handlerStack->push($handlerClosure, $handler);

            // Add the booted handler here, so it can't be loaded again.

            $this->bootedHandlers[] = $handler;
        }

        if ($this->isMocking()) {
            $mockObject = $this->mockClient->guessNextResponse($this->request);

            // We'll attempt to get the mock response from the fixture - if it
            // returns null then we will use the fixture middleware.

            $mockResponse = $mockObject instanceof Fixture ? $mockObject->getMockResponse() : $mockObject;

            // If the mock response has been found we will register the normal
            // mock middleware.

            if ($mockResponse instanceof MockResponse) {
                $handlerStack->push(new MockMiddleware($mockResponse), 'saloonMockMiddleware');
            }

            // If it hasn't been found and the mock object is a fixture then
            // we will register the fixture recorder middleware.

            if (is_null($mockResponse) && $mockObject instanceof Fixture) {
                $this->request->setIsRecordingFixture(true);

                $handlerStack->push(new FixtureRecorderMiddleware($mockObject), 'saloonFixtureMiddleware');
            }
        }

        return $handlerStack;
    }
}
