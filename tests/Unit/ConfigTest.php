<?php

declare(strict_types=1);

use Saloon\Config;
use Saloon\Http\Response;
use Saloon\Http\PendingRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Senders\GuzzleSender;
use Saloon\Exceptions\StrayRequestException;
use Saloon\Tests\Fixtures\Senders\ArraySender;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Connectors\TestConnector;

afterEach(function () {
    Config::clearGlobalMiddleware();
    Config::$defaultSender = GuzzleSender::class;
});

test('the config can specify global middleware', function () {
    $mockClient = new MockClient([
        new MockResponse(['name' => 'Jake Owen - Beachin']),
    ]);

    $count = 0;

    Config::globalMiddleware()->onRequest(function (PendingRequest $pendingRequest) use (&$count) {
        $count++;
    });

    Config::globalMiddleware()->onResponse(function (Response $response) use (&$count) {
        $count++;
    });

    TestConnector::make()->send(new UserRequest, $mockClient);

    expect($count)->toBe(2);
});

test('you can change the global default sender used', function () {
    Config::$defaultSender = ArraySender::class;

    $connector = new TestConnector;

    $connector->send(new UserRequest);

    expect($connector->sender())->toBeInstanceOf(ArraySender::class);

    Config::$defaultSender = GuzzleSender::class;

    $connector = new TestConnector;

    $response = $connector->send(new UserRequest);

    expect($response->getPendingRequest()->getConnector()->sender())->toBeInstanceOf(GuzzleSender::class);
});

test('you can change how the global default sender is resolved', function () {
    $sender = TestConnector::make()->sender();

    expect($sender)->toBeInstanceOf(GuzzleSender::class);

    Config::setSenderResolver(static fn () => new ArraySender);

    $sender = TestConnector::make()->sender();

    expect($sender)->toBeInstanceOf(ArraySender::class);

    Config::setSenderResolver(null);

    $sender = TestConnector::make()->sender();

    expect($sender)->toBeInstanceOf(GuzzleSender::class);
});

test('you can prevent stray api requests', function () {
    Config::preventStrayRequests();

    $this->expectException(StrayRequestException::class);
    $this->expectExceptionMessage('Attempted to make a real API request! Make sure to use a mock response or fixture.');

    TestConnector::make()->send(new UserRequest);

    Config::clearGlobalMiddleware();
});
