<?php

declare(strict_types=1);

use Saloon\Http\PendingRequest;
use GuzzleHttp\Psr7\HttpFactory;
use Saloon\Http\Faking\MockResponse;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Promise\FulfilledPromise;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Requests\HasXmlBodyRequest;

test('the default body is loaded with the content type header', function () {
    $request = new HasXmlBodyRequest();

    expect($request->body()->all())->toEqual('<p>Howdy</p>');

    $connector = new TestConnector;
    $pendingRequest = $connector->createPendingRequest($request);

    expect($pendingRequest->headers()->get('Content-Type'))->toEqual('application/xml');
});

test('the guzzle sender properly sends it', function () {
    $connector = new TestConnector;
    $request = new HasXmlBodyRequest;

    $request->middleware()->onRequest(static function (PendingRequest $pendingRequest) {
        expect($pendingRequest->headers()->get('Content-Type'))->toEqual('application/xml');
    });

    $asserted = false;

    $connector->sender()->addMiddleware(function (callable $handler) use ($request, &$asserted) {
        return function (RequestInterface $guzzleRequest, array $options) use ($request, &$asserted) {
            expect($guzzleRequest->getHeader('Content-Type'))->toEqual(['application/xml']);
            expect((string)$guzzleRequest->getBody())->toEqual((string)$request->body());

            $asserted = true;

            $factory = new HttpFactory;

            return new FulfilledPromise(MockResponse::make()->createPsrResponse($factory, $factory));
        };
    });

    $connector->send($request);

    expect($asserted)->toBeTrue();
});
