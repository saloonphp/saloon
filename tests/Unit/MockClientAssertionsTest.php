<?php declare(strict_types=1);

use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Http\Faking\MockClient;
use Sammyjo20\Saloon\Http\Faking\MockResponse;
use Sammyjo20\Saloon\Http\Responses\SaloonResponse;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\UserRequest;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\ErrorRequest;

test('assertSent works with a request', function () {
    $mockClient = new MockClient([
        UserRequest::class => MockResponse::make(['name' => 'Sam'], 200),
    ]);

    (new UserRequest())->send($mockClient);

    $mockClient->assertSent(UserRequest::class);
});

test('assertSent works with a closure', function () {
    $mockClient = new MockClient([
        UserRequest::class => MockResponse::make(['name' => 'Sam'], 200),
        ErrorRequest::class => MockResponse::make(['error' => 'Server Error'], 500),
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

test('assertSent works with a url', function () {
    $mockClient = new MockClient([
        UserRequest::class => MockResponse::make(['name' => 'Sam'], 200),
    ]);

    (new UserRequest())->send($mockClient);

    $mockClient->assertSent('saloon.dev/*');
    $mockClient->assertSent('/user');
    $mockClient->assertSent('api/user');
});

test('assertNotSent works with a request', function () {
    $mockClient = new MockClient([
        UserRequest::class => MockResponse::make(['name' => 'Sam'], 200),
        ErrorRequest::class => MockResponse::make(['error' => 'Server Error'], 500),
    ]);

    (new ErrorRequest())->send($mockClient);

    $mockClient->assertNotSent(UserRequest::class);
});

test('assertNotSent works with a closure', function () {
    $mockClient = new MockClient([
        UserRequest::class => MockResponse::make(['name' => 'Sam'], 200),
        ErrorRequest::class => MockResponse::make(['error' => 'Server Error'], 500),
    ]);

    $originalRequest = new ErrorRequest();
    $originalResponse = $originalRequest->send($mockClient);

    $mockClient->assertNotSent(function ($request) {
        return $request instanceof UserRequest;
    });
});

test('assertNotSent works with a url', function () {
    $mockClient = new MockClient([
        UserRequest::class => MockResponse::make(['name' => 'Sam'], 200),
    ]);

    (new UserRequest())->send($mockClient);

    $mockClient->assertNotSent('google.com/*');
    $mockClient->assertNotSent('/error');
});

test('assertSentJson works properly', function () {
    $mockClient = new MockClient([
        UserRequest::class => MockResponse::make(['name' => 'Sam'], 200),
    ]);

    (new UserRequest())->send($mockClient);

    $mockClient->assertSentJson(UserRequest::class, [
        'name' => 'Sam',
    ]);
});

test('assertSentJson works with multiple requests in history', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam'], 200),
        MockResponse::make(['name' => 'Taylor'], 201),
        MockResponse::make(['name' => 'Marcel'], 204),
    ]);

    (new UserRequest())->send($mockClient);
    (new UserRequest())->send($mockClient);
    (new UserRequest())->send($mockClient);

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
        UserRequest::class => MockResponse::make(['name' => 'Sam'], 200),
    ]);

    $mockClient->assertNothingSent();
});

test('assertSentCount works properly', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam'], 200),
        MockResponse::make(['name' => 'Taylor'], 200),
        MockResponse::make(['name' => 'Marcel'], 200),
    ]);

    (new UserRequest())->send($mockClient);
    (new UserRequest())->send($mockClient);
    (new UserRequest())->send($mockClient);

    $mockClient->assertSentCount(3);
});

test('assertSent with a closure works with more than one request in the history', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam'], 200),
        MockResponse::make(['name' => 'Taylor'], 201),
        MockResponse::make(['name' => 'Marcel'], 204),
    ]);

    (new UserRequest())->send($mockClient);
    (new UserRequest())->send($mockClient);
    (new UserRequest())->send($mockClient);

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
