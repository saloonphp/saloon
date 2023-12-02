<?php

declare(strict_types=1);

use Saloon\Data\MultipartValue;
use Saloon\Http\PendingRequest;
use GuzzleHttp\Psr7\HttpFactory;
use Saloon\Http\Faking\MockResponse;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Promise\FulfilledPromise;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Repositories\Body\MultipartBodyRepository;
use Saloon\Tests\Fixtures\Requests\MixedMultipartRequest;
use Saloon\Tests\Fixtures\Requests\HasMultipartBodyRequest;
use Saloon\Tests\Fixtures\Connectors\HasMultipartBodyConnector;

test('the default body is loaded with the content type header', function () {
    $request = new HasMultipartBodyRequest();

    expect($request->body()->all())->toEqual([
        new MultipartValue('nickname', 'Sam', 'user.txt', ['X-Saloon' => 'Yee-haw!']),
    ]);

    $connector = new TestConnector;
    $pendingRequest = $connector->createPendingRequest($request);

    /** @var MultipartBodyRepository $body */
    $body = $pendingRequest->body();

    expect($pendingRequest->headers()->get('Content-Type'))->toEqual('multipart/form-data; boundary=' . $body->getBoundary());
});

test('when both the connector and the request have the same request bodies they will be merged', function () {
    $connector = new HasMultipartBodyConnector;
    $request = new HasMultipartBodyRequest;

    expect($connector->body()->all())->toEqual([
        new MultipartValue('nickname', 'Gareth', 'user.txt', ['X-Saloon' => 'Yee-haw!']),
        new MultipartValue('drink', 'Moonshine', 'moonshine.txt', ['X-My-Head' => 'Spinning!']),
    ]);

    expect($request->body()->all())->toEqual([
        new MultipartValue('nickname', 'Sam', 'user.txt', ['X-Saloon' => 'Yee-haw!']),
    ]);

    // Nickname should be overwritten to "Sam" and "drink" should be merged in

    $pendingRequest = $connector->createPendingRequest($request);
    $pendingRequestBody = $pendingRequest->body();

    expect($pendingRequestBody)->toBeInstanceOf(MultipartBodyRepository::class);

    expect($pendingRequestBody->all())->toEqual([
        new MultipartValue('nickname', 'Gareth', 'user.txt', ['X-Saloon' => 'Yee-haw!']),
        new MultipartValue('drink', 'Moonshine', 'moonshine.txt', ['X-My-Head' => 'Spinning!']),
        new MultipartValue('nickname', 'Sam', 'user.txt', ['X-Saloon' => 'Yee-haw!']),
    ]);
});

test('the guzzle sender properly sends it', function () {
    $connector = new TestConnector;
    $request = new HasMultipartBodyRequest;

    $asserted = false;

    $request->middleware()->onRequest(static function (PendingRequest $pendingRequest) {
        expect($pendingRequest->headers()->get('Content-Type'))->toContain('multipart/form-data; boundary=' . $pendingRequest->body()->getBoundary());
    });

    $connector->sender()->addMiddleware(function (callable $handler) use ($request, &$asserted) {
        return function (RequestInterface $guzzleRequest, array $options) use ($request, &$asserted) {
            expect($guzzleRequest->getHeader('Content-Type')[0])->toContain('multipart/form-data; boundary=');

            expect((string)$guzzleRequest->getBody())->toContain(
                'X-Saloon: Yee-haw!',
                'Content-Disposition: form-data; name="nickname"; filename="user.txt"',
                'Content-Length: 3',
                'Sam',
            );

            $asserted = true;

            $factory = new HttpFactory;

            return new FulfilledPromise(MockResponse::make()->createPsrResponse($factory, $factory));
        };
    });

    $connector->send($request);

    expect($asserted)->toBeTrue();
});

test('can send a real multipart request and files are sent', function () {
    $connector = new TestConnector;
    $request = new MixedMultipartRequest;

    $request->body()->add('name', 'Howdy');
    $request->body()->add('file', file_get_contents('tests/Fixtures/Howdy.txt'), 'hi.txt');

    $response = $connector->send($request);

    $data = $response->json();

    expect($data)->toHaveKey('name', 'Howdy');
    expect($data)->toHaveKey('file_contents', 'Hello World!' . PHP_EOL);
});

test('can send an empty string as the contents', function () {
    $connector = new TestConnector;
    $request = new MixedMultipartRequest;

    $request->body()->add('name', 'Howdy');
    $request->body()->add('file', '', 'hi.txt');

    $response = $connector->send($request);

    $data = $response->json();

    expect($data)->toHaveKey('name', 'Howdy');
    expect($data)->toHaveKey('file_contents', '');
});

test('can send multiple multipart files with the same key name', function () {
    $connector = new TestConnector;
    $request = new HasMultipartBodyRequest;

    $request->body()->add('nickname', 'Alfie', 'user.txt');
    $request->body()->add('nickname', 'Tom', 'user.txt');

    $asserted = false;

    $connector->sender()->addMiddleware(function (callable $handler) use ($request, &$asserted) {
        return function (RequestInterface $guzzleRequest, array $options) use ($request, &$asserted) {
            expect($guzzleRequest->getBody()->getContents())->toContain(
                'X-Saloon: Yee-haw!',
                'Content-Disposition: form-data; name="nickname"; filename="user.txt"',
                'Content-Length: 3',
                'Sam',
                'Alfie',
                'Tom',
            );

            $asserted = true;

            $factory = new HttpFactory;

            return new FulfilledPromise(MockResponse::make()->createPsrResponse($factory, $factory));
        };
    });

    $connector->send($request);

    expect($asserted)->toBeTrue();
});
