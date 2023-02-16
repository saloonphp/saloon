<?php

declare(strict_types=1);

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Tests\Fixtures\Requests\UserRequest;

test('async request delay works', function () {
    $request = new UserRequest;
    $request->delay()->set(1000);

    expect($request->delay()->all())->toEqual(1000);

    $start = microtime(true);
    connector()->sendAsync($request)->wait();
    expect(microtime(true) - $start)->toBeGreaterThan(1);

    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam']),
    ]);

    $start = microtime(true);
    connector()->sendAsync($request, $mockClient)->wait();
    expect(microtime(true) - $start)->toBeGreaterThan(1);
});

test('test request delay takes priority over connector delay', function () {
    $request = new UserRequest;

    $request
        ->delay()
        ->set(1000);

    expect($request->delay()->all())->toEqual(1000);

    $connector = connector();

    $start = microtime(true);
    $connector->send($request, new MockClient([
        MockResponse::make(['name' => 'Sam']),
    ]));
    $waitTime = microtime(true) - $start;
    expect($waitTime)->toBeGreaterThan(1);

    $connector = connector();
    $connector->delay()->set(5000);
    $start = microtime(true);
    $connector->send($request, new MockClient([
        MockResponse::make(['name' => 'Sam']),
    ]));
    $waitTime = microtime(true) - $start;

    expect($waitTime)->toBeLessThan(5);
});

test('request delay works', function () {
    $request = new UserRequest;
    $request->delay()->set(1000);

    expect($request->delay()->all())->toEqual(1000);

    $start = microtime(true);
    connector()->send($request);
    expect(microtime(true) - $start)->toBeGreaterThan(1);

    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam']),
    ]);

    $start = microtime(true);
    connector()->send($request, $mockClient);
    expect(microtime(true) - $start)->toBeGreaterThan(1);
});

test('connector delay works', function () {
    $request = new UserRequest;

    expect($request->delay()->isEmpty())->toBeTrue();

    $connector = connector();
    $connector->delay()->set(1000);

    expect($connector->delay()->isNotEmpty())->toBeTrue();
    expect($connector->delay()->all())->toBe(1000);

    $start = microtime(true);
    $connector->send($request, new MockClient([
        MockResponse::make(['name' => 'Sam']),
    ]));
    expect(microtime(true) - $start)->toBeGreaterThan(1);

    expect($connector->delay()->all())->toBe(1000);

    $start = microtime(true);
    $connector->send($request);
    expect(microtime(true) - $start)->toBeGreaterThan(1);
});
