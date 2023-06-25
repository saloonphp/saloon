<?php

declare(strict_types=1);

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\FakeResponse;
use Saloon\Http\Faking\MockResponse;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Connectors\TestConnector;

test('if a simulated response payload was provided before mock response it will take priority', function () {
    $mockClient = new MockClient([
        new MockResponse(['name' => 'Sam'], 200, ['X-Greeting' => 'Howdy']),
    ]);

    $fakeResponse = new FakeResponse(['name' => 'Gareth'], 201, ['X-Greeting' => 'Hello']);

    $request = new UserRequest;
    $request->middleware()->onRequest(fn () => $fakeResponse);

    $response = TestConnector::make()->send($request, $mockClient);

    expect($response->json())->toEqual(['name' => 'Gareth']);
    expect($response->status())->toEqual(201);
    expect($response->header('X-Greeting'))->toEqual('Hello');
});
