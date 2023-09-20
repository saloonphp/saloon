<?php

use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Psr7\HttpFactory;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\RequestInterface;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\PendingRequest;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Requests\HasJsonBodyRequest;
use Saloon\Tests\Fixtures\Requests\UserRequest;

test('you can provide digest authentication and guzzle will send it', function () {
    $connector = new TestConnector;
    $request = new UserRequest;

    $request->withDigestAuth('Sammyjo20', 'Cowboy1', 'Howdy');

    $asserted = false;

    $connector->sender()->addMiddleware(function (callable $handler) use ($request, &$asserted) {
        return function (RequestInterface $guzzleRequest, array $options) use ($request, &$asserted) {
            expect($options)->toHaveKey(RequestOptions::AUTH, [
                'Sammyjo20',
                'Cowboy1',
                'Howdy',
            ]);

            $asserted = true;

            $factory = new HttpFactory;

            return new FulfilledPromise(MockResponse::make()->createPsrResponse($factory, $factory));
        };
    });

    $connector->send($request);

    expect($asserted)->toBeTrue();
});
