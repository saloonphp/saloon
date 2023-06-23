<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\HttpFactory;
use Saloon\Http\Faking\MockResponse;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Promise\FulfilledPromise;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Requests\HasStreamBodyRequest;

test('the default body is loaded', function () {
    $request = new HasStreamBodyRequest;

    expect($request->body()->all())->toBeInstanceOf(StreamInterface::class);
    expect($request->body()->get())->toBeInstanceOf(StreamInterface::class);
    expect((string)$request->body())->toEqual('Howdy, Partner');
});

test('the guzzle sender properly sends it', function () {
    $connector = new TestConnector;
    $request = new HasStreamBodyRequest;

    $request->headers()->add('Content-Type', 'application/custom');

    $connector->sender()->addMiddleware(function (callable $handler) use ($request) {
        return function (RequestInterface $guzzleRequest, array $options) use ($request) {
            expect($guzzleRequest->getHeader('Content-Type'))->toEqual(['application/custom']);
            expect((string)$guzzleRequest->getBody())->toEqual('Howdy, Partner');

            $factory = new HttpFactory;

            return new FulfilledPromise(MockResponse::make()->createPsrResponse($factory, $factory));
        };
    });

    $connector->send($request);
});
