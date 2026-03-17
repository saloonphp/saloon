<?php

declare(strict_types=1);

use Saloon\Http\Faking\MockClient;
use Saloon\Helpers\StatusCodeHelper;
use Saloon\Http\Faking\MockResponse;
use Saloon\Exceptions\Request\ClientException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Exceptions\Request\Statuses\NotFoundException;
use Saloon\Exceptions\Request\Statuses\ForbiddenException;
use Saloon\Tests\Fixtures\Requests\AlwaysHasFailureRequest;
use Saloon\Exceptions\Request\Statuses\UnauthorizedException;
use Saloon\Exceptions\Request\Statuses\GatewayTimeoutException;
use Saloon\Exceptions\Request\Statuses\RequestTimeOutException;
use Saloon\Exceptions\Request\Statuses\PaymentRequiredException;
use Saloon\Exceptions\Request\Statuses\TooManyRequestsException;
use Saloon\Exceptions\Request\Statuses\MethodNotAllowedException;
use Saloon\Exceptions\Request\Statuses\ServiceUnavailableException;
use Saloon\Exceptions\Request\Statuses\InternalServerErrorException;
use Saloon\Exceptions\Request\Statuses\UnprocessableEntityException;

test('the response will return different exceptions based on status', function (int $status, string $expectedException) {
    $mockClient = new MockClient([
        MockResponse::make(['message' => 'Oh yee-naw!'], $status),
    ]);

    $response = TestConnector::make()->send(new UserRequest, $mockClient);
    $exception = $response->toException();

    $message = sprintf('%s (%s) Response: %s', StatusCodeHelper::getMessage($status), $status, $response->body());

    expect($exception)->toBeInstanceOf($expectedException);
    expect($exception->getMessage())->toEqual($message);
})->with([
    [401, UnauthorizedException::class],
    [402, PaymentRequiredException::class],
    [403, ForbiddenException::class],
    [404, NotFoundException::class],
    [405, MethodNotAllowedException::class],
    [408, RequestTimeOutException::class],
    [422, UnprocessableEntityException::class],
    [429, TooManyRequestsException::class],
    [500, InternalServerErrorException::class],
    [503, ServiceUnavailableException::class],
    [504, GatewayTimeoutException::class],
    [418, ClientException::class],
    [411, ClientException::class],
]);

test('when the failed method is customised the response will return ok request exceptions', function (int $status, string $expectedException) {
    $mockClient = new MockClient([
        MockResponse::make(['message' => 'Oh yee-naw!'], $status),
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
