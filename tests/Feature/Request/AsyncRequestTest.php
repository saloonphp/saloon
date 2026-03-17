<?php

declare(strict_types=1);

use Saloon\Http\Response;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use GuzzleHttp\Promise\PromiseInterface;
use Saloon\Tests\Fixtures\Responses\UserData;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Requests\ErrorRequest;
use Saloon\Tests\Fixtures\Responses\UserResponse;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Requests\UserRequestWithCustomResponse;

test('an asynchronous request can be made successfully', function () {
    $promise = TestConnector::make()->sendAsync(new UserRequest);

    expect($promise)->toBeInstanceOf(PromiseInterface::class);

    $response = $promise->wait();

    expect($response)->toBeInstanceOf(Response::class);

    $data = $response->json();

    expect($response->getPendingRequest()->isAsynchronous())->toBeTrue();
    expect($response->isMocked())->toBeFalse();
    expect($response->status())->toEqual(200);

    expect($data)->toEqual([
        'name' => 'Sammyjo20',
        'actual_name' => 'Sam',
        'twitter' => '@carre_sam',
    ]);
});

test('an asynchronous request can handle an exception properly', function () {
    $promise = TestConnector::make()->sendAsync(new ErrorRequest);

    $this->expectException(RequestException::class);

    $promise->wait();
});

test('an asynchronous response will still be passed through response middleware', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam']),
    ]);

    $request = new UserRequest();

    $passed = false;

    $request->middleware()->onResponse(function (Response $response) use (&$passed) {
        $passed = true;
    });

    $connector = new TestConnector;

    $promise = $connector->sendAsync($request, $mockClient);
    $response = $promise->wait();

    expect($passed)->toBeTrue();
});

test('an asynchronous request will return a custom response', function () {
    $mockClient = new MockClient([
        MockResponse::make(['foo' => 'bar']),
    ]);

    $connector = new TestConnector;
    $request = new UserRequestWithCustomResponse();

    $promise = $connector->sendAsync($request, $mockClient);

    $response = $promise->wait();

    expect($response)->toBeInstanceOf(UserResponse::class);
    expect($response)->customCastMethod()->toBeInstanceOf(UserData::class);
    expect($response)->foo()->toBe('bar');
});

test('middleware is only executed when an asynchronous request is sent', function () {
    $mockClient = new MockClient([
        MockResponse::make(['foo' => 'bar']),
    ]);

    $request = new UserRequest;
    $request->withMockClient($mockClient);
    $sent = false;

    $request->middleware()->onRequest(function () use (&$sent) {
        $sent = true;
    });

    $promise = TestConnector::make()->sendAsync($request);

    expect($sent)->toBeFalse();

    $promise->wait();

    expect($sent)->toBeTrue();
});
