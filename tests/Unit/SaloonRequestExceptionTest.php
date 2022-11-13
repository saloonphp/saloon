<?php declare(strict_types=1);

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Tests\Fixtures\Requests\UserRequest;

test('saloon request exception contains the guzzle exception', function () {
    $mockClient = new MockClient([
        MockResponse::make([], 500),
    ]);

    $response = (new UserRequest())->send($mockClient);
    $exception = $response->toException();

    expect($exception->getSaloonResponse())->toBe($response);
    expect($exception->getPrevious())->toBe($response->getGuzzleException());
});
