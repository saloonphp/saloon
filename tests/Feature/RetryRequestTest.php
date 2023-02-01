<?php

use Saloon\Exceptions\Request\Statuses\InternalServerErrorException;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Requests\ErrorRequest;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Symfony\Component\Stopwatch\Stopwatch;

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

test('a failed request can have an interval between each attempt', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam'], 500),
        MockResponse::make(['name' => 'Gareth'], 500),
        MockResponse::make(['name' => 'Teodor'], 200),
    ]);

    $connector = new TestConnector;
    $connector->withMockClient($mockClient);

    $stopwatch = new Stopwatch();
    $stopwatch->start('sendAndRetry');

    $connector->sendAndRetry(new UserRequest, 3, 1000);

    $duration = $stopwatch->stop('sendAndRetry')->getDuration();

    // It should be a duration of 2000ms (2 seconds) because the there are two requests
    // after the first.

    expect(floor($duration / 1000) * 1000)->toEqual(2000);
});

test('an exception other than a request exception will not be retried', function () {
    //
});

test('you can customise if the method should retry', function () {
    //
});

test('you can modify the pending request inside the retry handler', function () {
    //
});

test('retry against a real api', function () {
    // Todo: Build a test route which will only pass with a specific header added

    $request = new UserRequest;
    $request->middleware()->onRequest(fn () => ray()->count());
    $response = TestConnector::make()->sendAndRetry($request, 5);

    dd($response);
});
