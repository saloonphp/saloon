<?php

use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\Saloon\Tests\Resources\Requests\UserRequest;

test('a request can be mocked', function () {
    $mockClient = new MockClient([
        new MockResponse(['foo' => 'bar'], 200)
    ]);

    dd($mockClient->getNextResponse()->getData());

    $request = new UserRequest();
});
