<?php

use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Exceptions\SaloonRequestException;
use Sammyjo20\Saloon\Tests\Resources\Requests\UserRequest;

test('you can get the original request options', function () {
    $mockClient = new MockClient([
        new MockResponse(['foo' => 'bar'], 200, ['X-Custom-Header' => 'Howdy']),
    ]);

    $response = (new UserRequest())->send($mockClient);

    $options = $response->getRequestOptions();

    expect($options)->toBeArray();
    expect($options['headers'])->toEqual(['Accept' => 'application/json']);
});

test('you can get the original request', function () {
    $mockClient = new MockClient([
        new MockResponse(['foo' => 'bar'], 200, ['X-Custom-Header' => 'Howdy']),
    ]);

    $request = new UserRequest;
    $response = $request->send($mockClient);

    expect($response->getOriginalRequest())->toBe($request);
});

test('it will throw an exception when you use the throw method', function () {
    $mockClient = new MockClient([
        new MockResponse([], 500),
    ]);

    $response = (new UserRequest())->send($mockClient);

    $this->expectException(SaloonRequestException::class);

    $response->throw();
});

test('to exception will return a saloon request exception', function () {
    $mockClient = new MockClient([
        new MockResponse([], 500),
    ]);

    $response = (new UserRequest())->send($mockClient);
    $exception = $response->toException();

    expect($exception)->toBeInstanceOf(SaloonRequestException::class);
});

test('the onError method will run a custom closure', function () {
    $mockClient = new MockClient([
        new MockResponse([], 500),
    ]);

    $response = (new UserRequest())->send($mockClient);
    $count = 0;

    $response->onError(function () use (&$count) {
        $count++;
    });

    expect($count)->toBe(1);
});
