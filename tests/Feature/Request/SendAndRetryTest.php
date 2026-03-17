<?php

declare(strict_types=1);

use Saloon\Http\Request;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Tests\Fixtures\Requests\HeaderErrorRequest;
use Saloon\Exceptions\Request\Statuses\InternalServerErrorException;

test('a failed request can be retried', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam'], 500),
        MockResponse::make(['name' => 'Gareth'], 500),
        MockResponse::make(['name' => 'Teodor'], 200),
    ]);

    $connector = new TestConnector;
    $connector->withMockClient($mockClient);

    $response = $connector->sendAndRetry(new UserRequest, 3);

    expect($response->status())->toBe(200);
    expect($response->json())->toEqual(['name' => 'Teodor']);

    $mockClient->assertSentCount(3);
});

test('if the attempts are exhausted it will throw an exception from the last request', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam'], 500),
        MockResponse::make(['name' => 'Gareth'], 500),
        MockResponse::make(['name' => 'Teodor'], 500),
    ]);

    $connector = new TestConnector;
    $connector->withMockClient($mockClient);

    $hitException = false;

    try {
        $connector->sendAndRetry(new UserRequest, 3);
    } catch (Exception $exception) {
        expect($exception)->toBeInstanceOf(InternalServerErrorException::class);
        expect($exception->getResponse()->json())->toEqual(['name' => 'Teodor']);

        $hitException = true;
    }

    expect($hitException)->toBeTrue();
    $mockClient->assertSentCount(3);
});

test('if the attempts are exhausted it will return the last response if throwing is disabled', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam'], 500),
        MockResponse::make(['name' => 'Gareth'], 500),
        MockResponse::make(['name' => 'Teodor'], 500),
    ]);

    $connector = new TestConnector;
    $connector->withMockClient($mockClient);

    $response = $connector->sendAndRetry(new UserRequest, 3, throw: false);

    expect($response->json())->toEqual(['name' => 'Teodor']);

    $mockClient->assertSentCount(3);
});

test('if a fatal request exception happens even with throw disabled it will throw the fatal request exception', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam'], 500),
        MockResponse::make(['name' => 'Gareth'], 500),
        MockResponse::make(['name' => 'Teodor'], 500)->throw(fn ($pendingRequest) => new FatalRequestException(new Exception(), $pendingRequest)),
    ]);

    $connector = new TestConnector;
    $connector->withMockClient($mockClient);

    $this->expectException(FatalRequestException::class);

    $connector->sendAndRetry(new UserRequest, 3, throw: false);
});

test('a failed request can have an interval between each attempt', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam'], 500),
        MockResponse::make(['name' => 'Gareth'], 500),
        MockResponse::make(['name' => 'Teodor'], 200),
    ]);

    $connector = new TestConnector;
    $connector->withMockClient($mockClient);

    $start = microtime(true);

    $connector->sendAndRetry(new UserRequest, 3, 1000);

    // It should be a duration of 2000ms (2 seconds) because the there are two requests
    // after the first.

    expect(round(microtime(true) - $start))->toBeGreaterThanOrEqual(2);
});

test('a failed request can have an interval with exponential backoff between each attempt', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam'], 500), // 1,000
        MockResponse::make(['name' => 'Gareth'], 500), // 2,000
        MockResponse::make(['name' => 'Michael'], 500), // 4,000
        MockResponse::make(['name' => 'Teodor'], 200),
    ]);

    $connector = new TestConnector;
    $connector->withMockClient($mockClient);

    $start = microtime(true);

    $connector->sendAndRetry(new UserRequest, 4, 1000, useExponentialBackoff: true);

    // It should be a duration of > 7000ms (7 seconds) because the there are four requests
    // after the first.

    expect(round(microtime(true) - $start))->toBeGreaterThanOrEqual(7);
});

test('an exception other than a request exception will not be retried', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam'], 500),
        MockResponse::make(['name' => 'Gareth'], 500),
        MockResponse::make(['name' => 'Teodor'], 200),
    ]);

    $connector = new TestConnector;
    $connector->withMockClient($mockClient);

    $connector->middleware()->onResponse(fn () => throw new Exception('Yee-naw!'));

    $hitException = false;

    try {
        $connector->sendAndRetry(new UserRequest, 3);
    } catch (Exception $ex) {
        expect($ex->getMessage())->toEqual('Yee-naw!');
        $hitException = true;
    }

    expect($hitException)->toBeTrue();

    $mockClient->assertSentCount(1);
});

test('you can customise if the method should retry', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam'], 500),
        MockResponse::make(['name' => 'Gareth'], 500),
        MockResponse::make(['name' => 'Teodor'], 200),
    ]);

    $connector = new TestConnector;
    $connector->withMockClient($mockClient);

    $this->expectException(InternalServerErrorException::class);
    $this->expectExceptionMessage('Internal Server Error (500) Response: {"name":"Gareth"}');

    $connector->sendAndRetry(new UserRequest, 3, 0, function (RequestException $exception, Request $request) {
        return $exception->getResponse()->json() !== ['name' => 'Gareth'];
    });
});

test('if the handle retry returns false it will throw an exception', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam'], 500),
        MockResponse::make(['name' => 'Gareth'], 500),
        MockResponse::make(['name' => 'Teodor'], 200),
    ]);

    $connector = new TestConnector;
    $connector->withMockClient($mockClient);

    $this->expectException(InternalServerErrorException::class);
    $this->expectExceptionMessage('Internal Server Error (500) Response: {"name":"Sam"}');

    $connector->sendAndRetry(new UserRequest, 3, 0, fn () => false);
});

test('if the handle retry returns false and throw option is disabled it will return a response', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam'], 500),
        MockResponse::make(['name' => 'Gareth'], 500),
        MockResponse::make(['name' => 'Teodor'], 200),
    ]);

    $connector = new TestConnector;
    $connector->withMockClient($mockClient);

    $response = $connector->sendAndRetry(new UserRequest, 5, 0, fn () => false, false);

    expect($response->status())->toBe(500);
    expect($response->json())->toEqual(['name' => 'Sam']);
});

test('if the handle retry returns false and throw option is disabled but a fatal request exception happens it will still throw', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam'], 500)->throw(fn ($pendingRequest) => new FatalRequestException(new Exception(), $pendingRequest)),
        MockResponse::make(['name' => 'Gareth'], 500),
        MockResponse::make(['name' => 'Teodor'], 200),
    ]);

    $connector = new TestConnector;
    $connector->withMockClient($mockClient);

    $this->expectException(FatalRequestException::class);

    $connector->sendAndRetry(new UserRequest, 5, 0, fn () => false, false);
});

test('you can modify the request inside the retry handler', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam'], 500),
        MockResponse::make(['name' => 'Gareth'], 500),
        MockResponse::make(['name' => 'Teodor'], 200),
    ]);

    $connector = new TestConnector;
    $connector->withMockClient($mockClient);

    $index = 0;

    $response = $connector->sendAndRetry(new UserRequest, 5, 0, function (Exception $exception, Request $request) use (&$index) {
        $index++;

        $request->headers()->add('X-Test-Index', $index);

        return true;
    });

    expect($response->status())->toBe(200);
    expect($response->json())->toEqual(['name' => 'Teodor']);
    expect($response->getPendingRequest()->headers()->get('X-Test-Index'))->toEqual(2);
});

test('retry against a live endpoint to test GuzzleSender', function () {
    $requestCount = 0;

    $connector = new TestConnector;

    $connector->middleware()->onRequest(function () use (&$requestCount) {
        $requestCount++;
    });

    $request = new HeaderErrorRequest();
    $index = 0;

    $response = $connector->sendAndRetry($request, 6, 0, function (Exception $exception, Request $request) use (&$exceptions, &$index) {
        $request->headers()->add('X-Yee-Haw', $index++);

        return true;
    });

    // Request count is five because:
    // Request 1 - no header
    // Request 2 - header but 0
    // Request 3 - header but 1
    // Request 4 - header but 2
    // Request 5 - header but 3

    expect($requestCount)->toEqual(5);
    expect($response->body())->toEqual('Success!');
});

test('you can authenticate the request inside the retry handler', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam'], 401),
        MockResponse::make(['name' => 'Gareth'], 200),
    ]);

    $connector = new TestConnector;
    $connector->withMockClient($mockClient);

    $response = $connector->sendAndRetry(new UserRequest, 2, 0, function (Exception $exception, Request $request) {
        $request->authenticate(new TokenAuthenticator('newToken'));

        return true;
    });

    expect($response->status())->toBe(200);
    expect($response->json())->toEqual(['name' => 'Gareth']);
    expect($response->getPendingRequest()->headers()->get('Authorization'))->toEqual('Bearer newToken');
});

test('the response pipeline is only executed once when retrying', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam'], 500),
        MockResponse::make(['name' => 'Gareth'], 500),
    ]);

    $counter = 0;

    $connector = new TestConnector;
    $connector->withMockClient($mockClient);

    $connector->middleware()->onResponse(function () use (&$counter) {
        $counter++;
    });

    $response = $connector->sendAndRetry(new UserRequest, 2, throw: false);

    expect($response->status())->toBe(500);
    expect($response->json())->toEqual(['name' => 'Gareth']);

    // Counter should be 2 as we have sent to requests

    expect($counter)->toBe(2);
});
