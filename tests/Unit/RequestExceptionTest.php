<?php

use Saloon\Exceptions\Request\ClientException;
use Saloon\Exceptions\Request\InternalServerErrorException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Exceptions\Request\ServerException;
use Saloon\Helpers\StatusCodeHelper;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Requests\AlwaysHasFailureRequest;
use Saloon\Tests\Fixtures\Requests\UserRequest;

test('the response will return different exceptions based on status', function (int $status, string $expectedException) {
    $mockClient = new MockClient([
        MockResponse::make(['message' => 'Oh yee-naw!'], $status)
    ]);

    $response = TestConnector::make()->send(new UserRequest, $mockClient);
    $exception = $response->toException();

    $message = sprintf('%s (%s) Response: %s', StatusCodeHelper::getMessage($status), $status, $response->body());

    expect($exception)->toBeInstanceOf($expectedException);
    expect($exception->getMessage())->toEqual($message);
})->with([
    [500, InternalServerErrorException::class],
    [504, ServerException::class],
    [422, ClientException::class],
    [401, ClientException::class],
]);

test('when the failed method is customised the response will return ok request exceptions', function (int $status, string $expectedException) {
    $mockClient = new MockClient([
        MockResponse::make(['message' => 'Oh yee-naw!'], $status)
    ]);

    $response = TestConnector::make()->send(new AlwaysHasFailureRequest, $mockClient);
    $exception = $response->toException();

    $message = sprintf('%s (%s) Response: %s', StatusCodeHelper::getMessage($status), $status, $response->body());

    expect($exception)->toBeInstanceOf($expectedException);
    expect($exception->getMessage())->toEqual($message);
})->with([
    [302, RequestException::class],
    [200, RequestException::class],
    [201, RequestException::class],
    [100, RequestException::class],
]);

