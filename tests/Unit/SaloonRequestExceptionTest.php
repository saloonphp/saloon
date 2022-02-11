<?php

use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Tests\Resources\Requests\UserRequest;

test('saloon request exception contains the guzzle exception', function () {
    $mockClient = new MockClient([
        MockResponse::make([], 500),
    ]);

    $response = (new UserRequest())->send($mockClient);
    $exception = $response->toException();

    expect($exception->getSaloonResponse())->toBe($response);
    expect($exception->getPrevious())->toBe($response->getGuzzleException());
});
