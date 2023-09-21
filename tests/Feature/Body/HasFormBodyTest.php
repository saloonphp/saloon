<?php

declare(strict_types=1);

use Saloon\Http\PendingRequest;
use GuzzleHttp\Psr7\HttpFactory;
use Saloon\Http\Faking\MockResponse;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Promise\FulfilledPromise;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Requests\HasFormBodyRequest;

test('the default body is loaded with the content type header', function () {
    $request = new HasFormBodyRequest();

    expect($request->body()->all())->toEqual([
        'name' => 'Sam',
        'catchphrase' => 'Yeehaw!',
    ]);

    $connector = new TestConnector;
    $pendingRequest = $connector->createPendingRequest($request);

    expect($pendingRequest->headers()->get('Content-Type'))->toEqual('application/x-www-form-urlencoded');
});

test('the guzzle sender properly sends it', function () {
    $connector = new TestConnector;
    $request = new HasFormBodyRequest;

    $request->middleware()->onRequest(static function (PendingRequest $pendingRequest) {
        expect($pendingRequest->headers()->get('Content-Type'))->toEqual('application/x-www-form-urlencoded');
    });

    $asserted = false;

    $connector->sender()->addMiddleware(function (callable $handler) use ($request, &$asserted) {
        return function (RequestInterface $guzzleRequest, array $options) use ($request, &$asserted) {
            expect($guzzleRequest->getHeader('Content-Type'))->toEqual(['application/x-www-form-urlencoded']);
            expect((string)$guzzleRequest->getBody())->toEqual((string)$request->body());

            $asserted = true;

            $factory = new HttpFactory;

            return new FulfilledPromise(MockResponse::make()->createPsrResponse($factory, $factory));
        };
    });

    $connector->send($request);

    expect($asserted)->toBeTrue();
});
