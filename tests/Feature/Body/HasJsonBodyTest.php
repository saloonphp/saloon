<?php

declare(strict_types=1);

use Saloon\Http\PendingRequest;
use Saloon\Http\Faking\MockResponse;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Promise\FulfilledPromise;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Requests\HasJsonBodyRequest;

test('the default body is loaded', function () {
    $request = new HasJsonBodyRequest();

    expect($request->body()->all())->toEqual([
        'name' => 'Sam',
        'catchphrase' => 'Yeehaw!',
    ]);
});

test('the content-type header is set in the pending request', function () {
    $request = new HasJsonBodyRequest();

    $pendingRequest = TestConnector::make()->createPendingRequest($request);

    expect($pendingRequest->headers()->all())->toEqual([
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
    ]);
});

test('the guzzle sender properly sends it', function () {
    $connector = new TestConnector;
    $request = new HasJsonBodyRequest;

    $request->middleware()->onRequest(static function (PendingRequest $pendingRequest) {
        expect($pendingRequest->headers()->get('Content-Type'))->toEqual('application/json');
    });

    $connector->sender()->addMiddleware(function (callable $handler) use ($request) {
        return function (RequestInterface $guzzleRequest, array $options) use ($request) {
            expect($guzzleRequest->getHeader('Content-Type'))->toEqual(['application/json']);
            expect((string)$guzzleRequest->getBody())->toEqual((string)$request->body());

            return new FulfilledPromise(MockResponse::make()->getPsrResponse());
        };
    });

    $connector->send($request);
});

test('you can specify different json flags that the body repository should use', function () {
    $request = new HasJsonBodyRequest();
    $body = $request->body();

    // We'll add a property with slashes

    $body->add('url', 'https://docs.saloon.dev');

    // By default, PHP will escape slashes

    expect((string)$body)->toEqual('{"name":"Sam","catchphrase":"Yeehaw!","url":"https:\/\/docs.saloon.dev"}');

    // Now we'll customise the flags

    $body->setJsonFlags(JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);

    expect((string)$body)->toEqual('{"name":"Sam","catchphrase":"Yeehaw!","url":"https://docs.saloon.dev"}');

    expect($body->getJsonFlags())->toEqual(JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR);
});

test('the JsonBodyRepository uses the JSON_THROW_ON_ERROR default flag', function () {
    $request = new HasJsonBodyRequest();
    $body = $request->body();

    expect($body->getJsonFlags())->toEqual(JSON_THROW_ON_ERROR);
});
