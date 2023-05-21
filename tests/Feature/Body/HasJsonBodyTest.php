<?php

declare(strict_types=1);

use Saloon\Exceptions\PendingRequestException;
use Saloon\Http\PendingRequest;
use Saloon\Http\Faking\MockResponse;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Promise\FulfilledPromise;
use Saloon\Repositories\Body\JsonBodyRepository;
use Saloon\Tests\Fixtures\Connectors\HasJsonBodyConnector;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Requests\HasJsonBodyRequest;
use Saloon\Tests\Fixtures\Requests\HasMultipartBodyRequest;

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

test('when both the connector and the request have the same request bodies they will be merged', function () {
    $connector = new HasJsonBodyConnector;
    $request = new HasJsonBodyRequest;

    expect($connector->body()->all())->toEqual([
        'name' => 'Gareth',
        'drink' => 'Moonshine'
    ]);

    expect($request->body()->all())->toEqual([
        'name' => 'Sam',
        'catchphrase' => 'Yeehaw!',
    ]);

    // Name should be overwritten to "Sam" and "catchphrase" should be merged in

    $pendingRequest = $connector->createPendingRequest($request);
    $pendingRequestBody = $pendingRequest->body();

    expect($pendingRequestBody)->toBeInstanceOf(JsonBodyRepository::class);

    expect($pendingRequestBody->all())->toEqual([
        'drink' => 'Moonshine',
        'name' => 'Sam',
        'catchphrase' => 'Yeehaw!',
    ]);
});

test('if the connector and request implement different body repositories then an exception is thrown', function () {
    $connector = new HasJsonBodyConnector;
    $request = new HasMultipartBodyRequest;

    $connector->createPendingRequest($request);
})->throws(PendingRequestException::class, 'Connector and request body types must be the same.');

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
