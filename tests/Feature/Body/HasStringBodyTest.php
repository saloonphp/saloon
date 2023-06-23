<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\HttpFactory;
use Saloon\Http\Faking\MockResponse;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Promise\FulfilledPromise;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Requests\HasStringBodyRequest;

test('the default body is loaded', function () {
    $request = new HasStringBodyRequest();

    expect($request->body()->all())->toEqual('name: Sam');
});

test('the guzzle sender properly sends it', function () {
    $connector = new TestConnector;
    $request = new HasStringBodyRequest;

    $request->headers()->add('Content-Type', 'application/custom');

    $asserted = false;

    $connector->sender()->addMiddleware(function (callable $handler) use ($request, &$asserted) {
        return function (RequestInterface $guzzleRequest, array $options) use ($request, &$asserted) {
            expect($guzzleRequest->getHeader('Content-Type'))->toEqual(['application/custom']);
            expect((string)$guzzleRequest->getBody())->toEqual((string)$request->body());

            $asserted = true;

            $factory = new HttpFactory;

            return new FulfilledPromise(MockResponse::make()->createPsrResponse($factory, $factory));
        };
    });

    $connector->send($request);

    expect($asserted)->toBeTrue();
});
