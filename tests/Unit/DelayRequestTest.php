<?php

declare(strict_types=1);

use Saloon\Contracts\Response;
use Saloon\Http\Faking\MockClient;
use Saloon\Contracts\PendingRequest;
use Saloon\Http\Faking\MockResponse;
use GuzzleHttp\Promise\RejectedPromise;
use GuzzleHttp\Promise\PromiseInterface;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Exceptions\TestResponseException;

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
    connector()->sendAsync($request,$mockClient)->wait();
    expect(microtime(true) - $start)->toBeGreaterThan(1);
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
    connector()->send($request,$mockClient);
    expect(microtime(true) - $start)->toBeGreaterThan(1);
});

