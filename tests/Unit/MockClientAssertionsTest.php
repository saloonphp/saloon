<?php

use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Http\SaloonResponse;
use Sammyjo20\Saloon\Tests\Resources\Requests\UserRequest;
use Sammyjo20\Saloon\Tests\Resources\Requests\ErrorRequest;

test('that assertSent works with a request', function () {
    $mockClient = new MockClient([
        UserRequest::class => new MockResponse(['name' => 'Sam'], 200),
    ]);

    (new UserRequest())->send($mockClient);

    $mockClient->assertSent(UserRequest::class);
});

test('that assertSent works with a closure', function () {
    $mockClient = new MockClient([
        UserRequest::class => new MockResponse(['name' => 'Sam'], 200),
        ErrorRequest::class => new MockResponse(['error' => 'Server Error'], 500),
    ]);

    $originalRequest = new UserRequest();
    $originalResponse = $originalRequest->send($mockClient);

    $mockClient->assertSent(function ($request, $response) use ($originalRequest, $originalResponse) {
        expect($request)->toBeInstanceOf(SaloonRequest::class);
        expect($response)->toBeInstanceOf(SaloonResponse::class);

        expect($request)->toBe($originalRequest);
        expect($response)->toBe($originalResponse);

        return true;
    });

    $newRequest = new ErrorRequest();
    $newResponse = $newRequest->send($mockClient);

    $mockClient->assertSent(function ($request, $response) use ($newRequest, $newResponse) {
        expect($request)->toBeInstanceOf(SaloonRequest::class);
        expect($response)->toBeInstanceOf(SaloonResponse::class);

        expect($request)->toBe($newRequest);
        expect($response)->toBe($newResponse);

        return true;
    });
});

test('that assertSent works with a url', function () {
    $mockClient = new MockClient([
        UserRequest::class => new MockResponse(['name' => 'Sam'], 200),
    ]);

    (new UserRequest())->send($mockClient);

    $mockClient->assertSent('samcarre.dev/*');
    $mockClient->assertSent('/user');
    $mockClient->assertSent('api/user');
});

test('that assertNotSent works with a request', function () {
    $mockClient = new MockClient([
        UserRequest::class => new MockResponse(['name' => 'Sam'], 200),
        ErrorRequest::class => new MockResponse(['error' => 'Server Error'], 500),
    ]);

    (new ErrorRequest())->send($mockClient);

    $mockClient->assertNotSent(UserRequest::class);
});

test('that assertNotSent works with a closure', function () {
    $mockClient = new MockClient([
        UserRequest::class => new MockResponse(['name' => 'Sam'], 200),
        ErrorRequest::class => new MockResponse(['error' => 'Server Error'], 500),
    ]);

    $originalRequest = new ErrorRequest();
    $originalResponse = $originalRequest->send($mockClient);

    $mockClient->assertNotSent(function ($request) {
        return $request instanceof UserRequest;
    });
});

test('that assertNotSent works with a url', function () {
    $mockClient = new MockClient([
        UserRequest::class => new MockResponse(['name' => 'Sam'], 200),
    ]);

    (new UserRequest())->send($mockClient);

    $mockClient->assertNotSent('google.com/*');
    $mockClient->assertNotSent('/error');
});

test('that assertSentJson works properly', function () {
    $mockClient = new MockClient([
        UserRequest::class => new MockResponse(['name' => 'Sam'], 200),
    ]);

    (new UserRequest())->send($mockClient);

    $mockClient->assertSentJson(UserRequest::class, [
        'name' => 'Sam',
    ]);
});

test('test assertNothingSent works properly', function () {
    $mockClient = new MockClient([
        UserRequest::class => new MockResponse(['name' => 'Sam'], 200),
    ]);

    $mockClient->assertNothingSent();
});

test('test assertSentCount works properly', function () {
    $mockClient = new MockClient([
        new MockResponse(['name' => 'Sam'], 200),
        new MockResponse(['name' => 'Taylor'], 200),
        new MockResponse(['name' => 'Marcel'], 200),
    ]);

    (new UserRequest())->send($mockClient);
    (new UserRequest())->send($mockClient);
    (new UserRequest())->send($mockClient);

    $mockClient->assertSentCount(3);
});
