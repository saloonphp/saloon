<?php declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Plugins;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Saloon\Http\SaloonRequest;
use Saloon\Http\SaloonConnector;

trait HasTestHandler
{
    /**
     * Boot a test handler that adds a simple header to the response.
     *
     * @return void
     */
    public function bootHasTestHandler(SaloonRequest $request)
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
