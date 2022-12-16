<?php

use GuzzleHttp\Promise\FulfilledPromise;
use Psr\Http\Message\RequestInterface;
use Saloon\Http\Faking\MockResponse;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Requests\HasBodyRequest;
use Saloon\Tests\Fixtures\Requests\HasFormBodyRequest;
use Saloon\Tests\Fixtures\Requests\HasXmlBodyRequest;

test('the default body is loaded', function () {
    $request = new HasBodyRequest();

    expect($request->body()->all())->toEqual('name: Sam');
});

test('the guzzle sender properly sends it', function () {
    $connector = new TestConnector;
    $request = new HasBodyRequest;

    $connector->sender()->addMiddleware(function (callable $handler) use ($request) {
        return function (RequestInterface $guzzleRequest, array $options) use ($request) {
            expect((string)$guzzleRequest->getBody())->toEqual((string)$request->body());

            return new FulfilledPromise(MockResponse::make()->getPsrResponse());
        };
    });

    $connector->send($request);
});
