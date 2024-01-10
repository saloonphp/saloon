<?php

declare(strict_types=1);

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Faking\GlobalMockClient;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Connectors\TestConnector;

test('can create a global mock client', function () {
    $mockClient = GlobalMockClient::make([
        MockResponse::make(['name' => 'Sam']),
    ]);

    expect($mockClient)->toBeInstanceOf(MockClient::class);
    expect(GlobalMockClient::get())->toBe($mockClient);

    $connector = new TestConnector;
    $response = $connector->send(new UserRequest);

    expect($response->isMocked())->toBeTrue();
    expect($response->json())->toEqual(['name' => 'Sam']);

    $mockClient->assertSent(UserRequest::class);
});

test('the mock client can be destroyed', function () {
    GlobalMockClient::destroy();

    expect(GlobalMockClient::get())->toBeNull();
});
