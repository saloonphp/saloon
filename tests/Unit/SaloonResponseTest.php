<?php

use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\Saloon\Clients\MockClient;
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
