<?php

declare(strict_types=1);

use Saloon\Data\MultipartValue;
use GuzzleHttp\Psr7\HttpFactory;
use Saloon\Http\Faking\MockResponse;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Promise\FulfilledPromise;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Repositories\Body\MultipartBodyRepository;
use Saloon\Tests\Fixtures\Requests\HasMultipartBodyRequest;
use Saloon\Tests\Fixtures\Connectors\HasMultipartBodyConnector;

test('the default body is loaded with the content type header', function () {
    $request = new HasMultipartBodyRequest();

    expect($request->body()->all())->toEqual([
        'nickname' => new MultipartValue('nickname', 'Sam', 'user.txt', ['X-Saloon' => 'Yee-haw!']),
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
        'nickname' => new MultipartValue('nickname', 'Gareth', 'user.txt', ['X-Saloon' => 'Yee-haw!']),
        'drink' => new MultipartValue('drink', 'Moonshine', 'moonshine.txt', ['X-My-Head' => 'Spinning!']),
    ]);

    expect($request->body()->all())->toEqual([
        'nickname' => new MultipartValue('nickname', 'Sam', 'user.txt', ['X-Saloon' => 'Yee-haw!']),
    ]);

    // Nickname should be overwritten to "Sam" and "drink" should be merged in

    $pendingRequest = $connector->createPendingRequest($request);
    $pendingRequestBody = $pendingRequest->body();

    expect($pendingRequestBody)->toBeInstanceOf(MultipartBodyRepository::class);

    expect($pendingRequestBody->all())->toEqual([
        'nickname' => new MultipartValue('nickname', 'Sam', 'user.txt', ['X-Saloon' => 'Yee-haw!']),
        'drink' => new MultipartValue('drink', 'Moonshine', 'moonshine.txt', ['X-My-Head' => 'Spinning!']),
    ]);
});

test('when both the connector and the request have the same request bodies the correct boundary header is used', function () {
    // This is going to be for when we have the multipart building
});

test('the guzzle sender properly sends it', function () {
    $connector = new TestConnector;
    $request = new HasMultipartBodyRequest;

    $connector->sender()->addMiddleware(function (callable $handler) use ($request) {
        return function (RequestInterface $guzzleRequest, array $options) use ($request) {
            expect($guzzleRequest->getHeader('Content-Type')[0])->toContain('multipart/form-data; boundary=');

            expect((string)$guzzleRequest->getBody())->toContain(
                'X-Saloon: Yee-haw!',
                'Content-Disposition: form-data; name="nickname"; filename="user.txt"',
                'Content-Length: 3',
                'Sam',
            );

            $factory = new HttpFactory;

            return new FulfilledPromise(MockResponse::make()->createPsrResponse($factory, $factory));
        };
    });

    $connector->send($request);
});
