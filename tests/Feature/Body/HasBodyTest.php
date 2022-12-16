<?php

declare(strict_types=1);

use Saloon\Http\Faking\MockResponse;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Promise\FulfilledPromise;
use Saloon\Tests\Fixtures\Requests\HasBodyRequest;
use Saloon\Tests\Fixtures\Connectors\TestConnector;

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
