<?php

declare(strict_types=1);

use Saloon\Http\Response;
use GuzzleHttp\Promise\Promise;
use Saloon\Http\PendingRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use GuzzleHttp\Promise\PromiseInterface;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Exceptions\TestResponseException;

test('an asynchronous request will return a saloon response on a successful request', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam']),
    ]);

    $request = new UserRequest;
    $promise = connector()->sendAsync($request, $mockClient);

    expect($promise)->toBeInstanceOf(PromiseInterface::class);

    $response = $promise->wait();

    expect($response)->toBeInstanceOf(Response::class);
    expect($response->json())->toEqual(['name' => 'Sam']);
    expect($response->status())->toEqual(200);
});

test('an asynchronous request will throw a saloon exception on an unsuccessful request', function () {
    $mockClient = new MockClient([
        MockResponse::make(['error' => 'Server Error'], 500),
    ]);

    $request = new UserRequest;
    $promise = connector()->sendAsync($request, $mockClient);

    expect($promise)->toBeInstanceOf(Promise::class);

    try {
        $promise->wait();
    } catch (Exception $exception) {
        expect($exception)->toBeInstanceOf(RequestException::class);

        $response = $exception->getResponse();

        expect($response)->toBeInstanceOf(Response::class);
        expect($response->json())->toEqual(['error' => 'Server Error']);
        expect($response->status())->toEqual(500);
        expect($response->toException())->toEqual($exception);
    }
});

test('an asynchronous request will throw an exception if a connection error happens', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Patrick'])->throw(fn (PendingRequest $pendingRequest) => new TestResponseException('Unable to connect!', $pendingRequest)),
    ]);

    $request = new UserRequest;
    $promise = connector()->sendAsync($request, $mockClient);

    try {
        $promise->wait();
    } catch (Exception $exception) {
        expect($exception)->toBeInstanceOf(TestResponseException::class);
        expect($exception->getMessage())->toEqual('Unable to connect!');
        expect($exception->getPendingRequest())->toBeInstanceOf(PendingRequest::class);
    }
});

test('if you chain an asynchronous request you can have a Response', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam'], 200),
    ]);

    $request = new UserRequest;
    $promise = connector()->sendAsync($request, $mockClient);

    $promise->then(
        function (Response $response) {
            expect($response)->toBeInstanceOf(Response::class);
        }
    );

    $promise->wait();
});

test('if you chain an erroneous asynchronous request the error can be caught in the rejection handler', function () {
    $mockClient = new MockClient([
        MockResponse::make(['error' => 'Server Error'], 500),
    ]);

    $request = new UserRequest;
    $promise = connector()->sendAsync($request, $mockClient);

    $promise = $promise->then(
        null,
        function (RequestException $exception) {
            $response = $exception->getResponse();

            expect($response)->toBeInstanceOf(Response::class);
            expect($response->status())->toEqual(500);
            expect($response->getRequestException())->toBe($exception);
        }
    );

    $promise->wait(false);
});

test('if a connection exception happens it will be provided in the rejection handler', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Patrick'])->throw(fn (PendingRequest $pendingRequest) => new TestResponseException('Unable to connect!', $pendingRequest)),
    ]);

    $request = new UserRequest;
    $promise = connector()->sendAsync($request, $mockClient);

    $promise = $promise->then(
        null,
        function ($exception) {
            expect($exception->getMessage())->toEqual('Unable to connect!');
        }
    );

    $promise->wait();
});
