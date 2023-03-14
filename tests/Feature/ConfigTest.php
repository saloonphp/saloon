<?php

use GuzzleHttp\Promise\FulfilledPromise;
use Psr\Http\Message\RequestInterface;
use Saloon\Http\Faking\MockResponse;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Requests\UserRequest;

test('default guzzle config options are sent', function () {
    $connector = new TestConnector;

    $connector->sender()->addMiddleware(function (callable $handler) {
        return function (RequestInterface $guzzleRequest, array $options) {
            expect($options)->toHaveKey('http_errors', true);
            expect($options)->toHaveKey('connect_timeout', 10);
            expect($options)->toHaveKey('timeout', 30);

            return new FulfilledPromise(MockResponse::make()->getPsrResponse());
        };
    });

    $connector->send(new UserRequest);
});

test('you can pass additional guzzle config options and they are merged from the connector and request', function () {
    $connector = new TestConnector();

    $connector->config()->add('debug', true);

    $connector->sender()->addMiddleware(function (callable $handler) {
        return function (RequestInterface $guzzleRequest, array $options) {
            expect($options)->toHaveKey('http_errors', true);
            expect($options)->toHaveKey('connect_timeout', 10);
            expect($options)->toHaveKey('timeout', 30);
            expect($options)->toHaveKey('debug', true);
            expect($options)->toHaveKey('verify', false);

            return new FulfilledPromise(MockResponse::make()->getPsrResponse());
        };
    });

    $request = new UserRequest;

    $request->config()->add('verify', false);

    $connector->send($request);
});
