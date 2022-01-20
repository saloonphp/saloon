<?php

namespace Sammyjo20\Saloon\Tests\Resources\Plugins;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Http\SaloonConnector;

trait HasTestHandler
{
    /**
     * Boot a test handler that adds a simple header to the response.
     *
     * @return void
     */
    public function bootHasTestHandlerFeature()
    {
        $this->addHandler('testHandler', function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                $promise = $handler($request, $options);

                return $promise->then(
                    function (ResponseInterface $response) {
                        $response = $response->withHeader('X-Test-Handler', true);

                        if ($this instanceof SaloonConnector) {
                            $response = $response->withHeader('X-Handler-Added-To', 'connector');
                        }

                        if ($this instanceof SaloonRequest) {
                            $response = $response->withHeader('X-Handler-Added-To', 'request');
                        }

                        return $response;
                    }
                );
            };
        });
    }
}
