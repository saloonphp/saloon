<?php declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Plugins;

use Saloon\Http\Request;
use Saloon\Http\Connector;
use Saloon\Http\PendingRequest;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Saloon\Exceptions\InvalidConnectorException;

trait HasTestHandler
{
    /**
     * Boot a test handler that adds a simple header to the response.
     *
     * @return void
     * @throws InvalidConnectorException
     */
    public function bootHasTestHandler(PendingRequest $pendingRequest)
    {
        $this->sender()->addMiddleware(function (callable $handler) {
            return function (RequestInterface $request, array $options) use ($handler) {
                $promise = $handler($request, $options);

                return $promise->then(
                    function (ResponseInterface $response) {
                        $response = $response->withHeader('X-Test-Handler', true);

                        if ($this instanceof Connector) {
                            $response = $response->withHeader('X-Handler-Added-To', 'connector');
                        }

                        if ($this instanceof Request) {
                            $response = $response->withHeader('X-Handler-Added-To', 'request');
                        }

                        return $response;
                    }
                );
            };
        }, 'testHandler');
    }
}
