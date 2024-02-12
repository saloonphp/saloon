<?php

declare(strict_types=1);

use Saloon\Http\PendingRequest;
use GuzzleHttp\Psr7\HttpFactory;
use Saloon\Http\Faking\MockResponse;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Promise\FulfilledPromise;
use Saloon\Exceptions\PendingRequestException;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Repositories\Body\JsonBodyRepository;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Requests\HasJsonBodyRequest;
use Saloon\Tests\Fixtures\Connectors\HasJsonBodyConnector;
use Saloon\Tests\Fixtures\Requests\HasMultipartBodyRequest;

test('the default body is loaded with the content type header', function () {
    $request = new HasJsonBodyRequest();

    expect($request->body()->all())->toEqual([
        'name' => 'Sam',
        'catchphrase' => 'Yeehaw!',
    ]);

    $connector = new TestConnector;
    $pendingRequest = $connector->createPendingRequest($request);

    expect($pendingRequest->headers()->get('Content-Type'))->toEqual('application/json');
});

test('the content-type header is set in the pending request', function () {
    $request = new HasJsonBodyRequest();

    $pendingRequest = TestConnector::make()->createPendingRequest($request);

    expect($pendingRequest->headers()->all())->toEqual([
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
    ]);
});

test('when just the connector has body the body will be sent', function () {
    $connector = new HasJsonBodyConnector;
    $request = new UserRequest;

    expect($connector->body()->all())->toEqual([
        'name' => 'Gareth',
        'drink' => 'Moonshine',
    ]);

    $pendingRequest = $connector->createPendingRequest($request);
    $pendingRequestBody = $pendingRequest->body();

    expect($pendingRequestBody)->toBeInstanceOf(JsonBodyRepository::class);

    expect($pendingRequestBody->all())->toEqual([
        'name' => 'Gareth',
        'drink' => 'Moonshine',
    ]);
});

test('when both the connector and the request have the same request bodies they will be merged', function () {
    $connector = new HasJsonBodyConnector;
    $request = new HasJsonBodyRequest;

    expect($connector->body()->all())->toEqual([
        'name' => 'Gareth',
        'drink' => 'Moonshine',
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

    $asserted = false;

    $connector->sender()->addMiddleware(function (callable $handler) use ($request, &$asserted) {
        return function (RequestInterface $guzzleRequest, array $options) use ($request, &$asserted) {
            expect($guzzleRequest->getHeader('Content-Type'))->toEqual(['application/json']);
            expect((string)$guzzleRequest->getBody())->toEqual((string)$request->body());

            $asserted = true;

            $factory = new HttpFactory;

            return new FulfilledPromise(MockResponse::make()->createPsrResponse($factory, $factory));
        };
    });

    $connector->send($request);

    expect($asserted)->toBeTrue();
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
