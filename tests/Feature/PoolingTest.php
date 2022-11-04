<?php

use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Sammyjo20\Saloon\Http\Responses\PsrResponse;
use Sammyjo20\Saloon\Tests\Fixtures\Connectors\TestConnector;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\ErrorRequest;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\UserRequest;

test('you can create a pool on a connector', function () {
    $connector = new TestConnector;

    $connector->sender()->addMiddleware(function (callable $handler) {
        return function (RequestInterface $request, array $options) use ($handler) {
            // ray($options)->blue();

            return $handler($request, $options);
        };
    });

    $requestA = new UserRequest;
    $requestB = new UserRequest;
    $requestC = new UserRequest;
    $requestD = new UserRequest;
    $requestE = new ErrorRequest;

    $pool = $connector->pool([
        $requestA,
        $requestB,
        $requestC,
        $requestD,
        $requestE,
    ]);

    $pool->setConcurrentRequests(10);

    $pool->then(function (PsrResponse $response) {
        ray($response)->green();
    });

    $pool->catch(function (Exception $ex) {
        ray($ex)->red();
    });

    $promise = $pool->send();

    $promise->wait();
});
