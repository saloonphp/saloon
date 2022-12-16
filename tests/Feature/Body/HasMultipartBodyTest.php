<?php

declare(strict_types=1);

use Saloon\Http\Faking\MockResponse;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Promise\FulfilledPromise;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Requests\HasMultipartBodyRequest;

test('the default body is loaded', function () {
    $request = new HasMultipartBodyRequest();

    expect($request->body()->all())->toEqual([
        [
            'name' => 'nickname',
            'contents' => 'Sam',
        ],
    ]);
});

test('the guzzle sender properly sends it', function () {
    $connector = new TestConnector;
    $request = new HasMultipartBodyRequest;

    $connector->sender()->addMiddleware(function (callable $handler) use ($request) {
        return function (RequestInterface $guzzleRequest, array $options) use ($request) {
            expect((string)$guzzleRequest->getBody())->toContain(
                'Content-Disposition: form-data; name="nickname"',
                'Content-Length: 3',
                'Sam',
            );

            return new FulfilledPromise(MockResponse::make()->getPsrResponse());
        };
    });

    $connector->send($request);
});
