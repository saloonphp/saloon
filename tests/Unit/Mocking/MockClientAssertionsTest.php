<?php

declare(strict_types=1);

use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Requests\ErrorRequest;
use PHPUnit\Framework\ExpectationFailedException;
use Saloon\Tests\Fixtures\Connectors\TestConnector;

test('assertSent works with a request', function () {
    $mockClient = new MockClient([
        UserRequest::class => MockResponse::make(['name' => 'Sam']),
    ]);

    connector()->send(new UserRequest, $mockClient);

    $mockClient->assertSent(UserRequest::class);
});

test('assertSent works with a closure', function () {
    $mockClient = new MockClient([
        UserRequest::class => MockResponse::make(['name' => 'Sam']),
        ErrorRequest::class => MockResponse::make(['error' => 'Server Error'], 500),
    ]);

    $originalRequest = new UserRequest();
    $originalResponse = connector()->send($originalRequest, $mockClient);

    $mockClient->assertSent(function ($request, $response) use ($originalRequest, $originalResponse) {
        expect($request)->toBeInstanceOf(Request::class);
        expect($response)->toBeInstanceOf(Response::class);

        expect($request)->toBe($originalRequest);
        expect($response)->toBe($originalResponse);

        return true;
    });

    $newRequest = new ErrorRequest();
    $newResponse = connector()->send($newRequest, $mockClient);

    $mockClient->assertSent(function ($request, $response) use ($newRequest, $newResponse) {
        expect($request)->toBeInstanceOf(Request::class);
        expect($response)->toBeInstanceOf(Response::class);

        expect($request)->toBe($newRequest);
        expect($response)->toBe($newResponse);

        return true;
    });
});

test('assertSent works with a url', function () {
    $mockClient = new MockClient([
        UserRequest::class => MockResponse::make(['name' => 'Sam']),
    ]);

    connector()->send(new UserRequest, $mockClient);

    $mockClient->assertSent('saloon.dev/*');
    $mockClient->assertSent('/user');
    $mockClient->assertSent('api/user');
});

test('assertNotSent works with a request', function () {
    $mockClient = new MockClient([
        UserRequest::class => MockResponse::make(['name' => 'Sam']),
        ErrorRequest::class => MockResponse::make(['error' => 'Server Error'], 500),
    ]);

    connector()->send(new ErrorRequest, $mockClient);

    $mockClient->assertNotSent(UserRequest::class);
});

test('assertNotSent works with a closure', function () {
    $mockClient = new MockClient([
        UserRequest::class => MockResponse::make(['name' => 'Sam']),
        ErrorRequest::class => MockResponse::make(['error' => 'Server Error'], 500),
    ]);

    $originalRequest = new ErrorRequest();
    $originalResponse = connector()->send($originalRequest, $mockClient);

    $mockClient->assertNotSent(function ($request) {
        return $request instanceof UserRequest;
    });
});

test('assertNotSent works with a url', function () {
    $mockClient = new MockClient([
        UserRequest::class => MockResponse::make(['name' => 'Sam']),
    ]);

    connector()->send(new UserRequest, $mockClient);

    $mockClient->assertNotSent('google.com/*');
    $mockClient->assertNotSent('/error');
});

test('assertSentJson works properly', function () {
    $mockClient = new MockClient([
        UserRequest::class => MockResponse::make(['name' => 'Sam']),
    ]);

    connector()->send(new UserRequest, $mockClient);

    $mockClient->assertSentJson(UserRequest::class, [
        'name' => 'Sam',
    ]);
});

test('assertSentJson works with multiple requests in history', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam']),
        MockResponse::make(['name' => 'Taylor']),
        MockResponse::make(['name' => 'Marcel']),
    ]);

    $connector = new TestConnector;

    $connector->send(new UserRequest, $mockClient);
    $connector->send(new UserRequest, $mockClient);
    $connector->send(new UserRequest, $mockClient);

    $mockClient->assertSentJson(UserRequest::class, [
        'name' => 'Sam',
    ]);

    $mockClient->assertSentJson(UserRequest::class, [
        'name' => 'Taylor',
    ]);

    $mockClient->assertSentJson(UserRequest::class, [
        'name' => 'Marcel',
    ]);
});

test('assertNothingSent works properly', function () {
    $mockClient = new MockClient([
        UserRequest::class => MockResponse::make(['name' => 'Sam']),
    ]);

    $mockClient->assertNothingSent();
});

test('assertSentCount works properly', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam']),
        MockResponse::make(['name' => 'Taylor']),
        MockResponse::make(['name' => 'Marcel']),
    ]);

    $connector = new TestConnector;

    $connector->send(new UserRequest, $mockClient);
    $connector->send(new UserRequest, $mockClient);
    $connector->send(new UserRequest, $mockClient);

    $mockClient->assertSentCount(3);
});

test('can assert count of requests', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam']),
        MockResponse::make(['name' => 'Taylor']),
        MockResponse::make(['name' => 'Marcel']),
        MockResponse::make(['message' => 'Error'], 500),
    ]);

    $connector = new TestConnector;

    $connector->send(new UserRequest, $mockClient);
    $connector->send(new UserRequest, $mockClient);
    $connector->send(new UserRequest, $mockClient);
    $connector->send(new ErrorRequest, $mockClient);

    $mockClient->assertSentCount(3, UserRequest::class);
    $mockClient->assertSentCount(1, ErrorRequest::class);
});

test('assertSent with a closure works with more than one request in the history', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam']),
        MockResponse::make(['name' => 'Taylor'], 201),
        MockResponse::make(['name' => 'Marcel'], 204),
    ]);

    $connector = new TestConnector;

    $connector->send(new UserRequest, $mockClient);
    $connector->send(new UserRequest, $mockClient);
    $connector->send(new UserRequest, $mockClient);

    $mockClient->assertSent(function ($request, $response) {
        return $response->json() === ['name' => 'Sam'] && $response->status() === 200;
    });

    $mockClient->assertSent(function ($request, $response) {
        return $response->json() === ['name' => 'Taylor'] && $response->status() === 201;
    });

    $mockClient->assertSent(function ($request, $response) {
        return $response->json() === ['name' => 'Marcel'] && $response->status() === 204;
    });
});

test('it can assert requests are sent in a specific order', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam']),
        MockResponse::make(['name' => 'Taylor'], 201),
        MockResponse::make(['name' => 'Marcel'], 204),
    ]);

    $connector = new TestConnector;

    $connector->send(new UserRequest, $mockClient);
    $connector->send(new UserRequest, $mockClient);
    $connector->send(new UserRequest, $mockClient);

    $mockClient->assertSentInOrder([
        UserRequest::class,
        function (UserRequest $request, Response $response) {
            return $response->json() === ['name' => 'Taylor'];
        },
        '/user',
    ]);
});

test('it can assert requests are sent in a specific order failure', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam']),
        MockResponse::make(['name' => 'Taylor'], 201),
        MockResponse::make(['name' => 'Marcel'], 204),
    ]);

    $connector = new TestConnector;

    $connector->send(new UserRequest(userId: 2), $mockClient);
    $connector->send(new UserRequest(userId: 1), $mockClient);
    $connector->send(new UserRequest(), $mockClient);

    $mockClient->assertSentInOrder([
        UserRequest::class,
        function (UserRequest $request) {
            return $request->userId === 2;
        },
        '/user',
    ]);
})->expectException(ExpectationFailedException::class);
