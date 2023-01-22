<?php

declare(strict_types=1);

use Saloon\Defaults;
use Saloon\Http\Response;
use Saloon\Http\PendingRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Connectors\TestConnector;

test('an application can specify global middleware', function () {
    $mockClient = new MockClient([
        new MockResponse(['name' => 'Jake Owen - Beachin']),
    ]);

    Defaults::middleware()->onRequest(function (PendingRequest $pendingRequest) {
        ray('Woohoo', $pendingRequest);
    });

    Defaults::middleware()->onRequest(function (PendingRequest $pendingRequest) {
        ray('Yo yo', $pendingRequest);
    });

    Defaults::middleware()->onResponse(function (Response $response) {
        ray('Response', $response);
    });

    TestConnector::make()->send(new UserRequest, $mockClient);
});

test('other test', function () {
    ray('hi');

    TestConnector::make()->send(new UserRequest);
});
