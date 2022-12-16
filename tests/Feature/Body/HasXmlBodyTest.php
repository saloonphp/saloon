<?php

use GuzzleHttp\Promise\FulfilledPromise;
use Psr\Http\Message\RequestInterface;
use Saloon\Http\Faking\MockResponse;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Requests\HasFormBodyRequest;
use Saloon\Tests\Fixtures\Requests\HasJsonBodyRequest;
use Saloon\Tests\Fixtures\Requests\HasXmlBodyRequest;

test('the default body is loaded', function () {
    $request = new HasXmlBodyRequest();

    expect($request->body()->all())->toEqual('<p>Howdy</p>');
});

test('the content-type header is set in the pending request', function () {
    $request = new HasXmlBodyRequest();

    $pendingRequest = TestConnector::make()->createPendingRequest($request);

    expect($pendingRequest->headers()->all())->toHaveKey('Content-Type', 'application/xml');
});

test('the guzzle sender properly sends it', function () {
    $connector = new TestConnector;
    $request = new HasXmlBodyRequest;

    $connector->sender()->addMiddleware(function (callable $handler) use ($request) {
        return function (RequestInterface $guzzleRequest, array $options) use ($request) {
            expect((string)$guzzleRequest->getBody())->toEqual((string)$request->body());

            return new FulfilledPromise(MockResponse::make()->getPsrResponse());
        };
    });

    $connector->send($request);
});
