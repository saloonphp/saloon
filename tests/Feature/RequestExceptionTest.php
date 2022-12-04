<?php

use GuzzleHttp\Exception\ServerException;
use Saloon\Contracts\Response;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Requests\ErrorRequest;
use Saloon\Tests\Fixtures\Requests\UserRequest;

test('you can use the to exception method to get the default RequestException exception with GuzzleSender', function () {
    $response = TestConnector::make()->send(new ErrorRequest);

    expect($response)->toBeInstanceOf(Response::class);

    $exception = $response->toException();

    expect($exception)->toBeInstanceOf(RequestException::class);
    expect($exception->getMessage())->toEqual($response->body());
    expect($exception->getPrevious())->toBeInstanceOf(ServerException::class);

    $this->expectExceptionObject($exception);

    $response->throw();
});

test('you can use the to exception method to get the default RequestException exception', function () {
    $mockClient = new MockClient([
        MockResponse::make(['message' => 'Server Error'], 500),
    ]);

    $response = TestConnector::make()->send(new UserRequest, $mockClient);

    expect($response)->toBeInstanceOf(Response::class);

    $exception = $response->toException();

    expect($exception)->toBeInstanceOf(RequestException::class);
    expect($exception->getMessage())->toEqual($response->body());

    // Previous is null with the SimulatedSender

    expect($exception->getPrevious())->toEqual(null);

    $this->expectExceptionObject($exception);

    $response->throw();
});

test('it throws exceptions properly with promises with GuzzleSender', function () {
    $promise = TestConnector::make()->sendAsync(new ErrorRequest);

    $correctInstance = false;

    $promise->otherwise(function (Throwable $exception) use (&$correctInstance) {
        if ($exception instanceof RequestException) {
            $correctInstance = true;
        }
    });

    try {
        $promise->wait();
    }  catch (Throwable $exception) {
        expect($correctInstance)->toBeTrue();
        expect($exception)->toBeInstanceOf(RequestException::class);
        expect($exception->getResponse())->toBeInstanceOf(Response::class);
        expect($exception->getMessage())->toEqual($exception->getResponse()->body());
        expect($exception->getPrevious())->toBeInstanceOf(ServerException::class);
    }
});

test('it throws exceptions properly with promises', function () {
    $mockClient = new MockClient([
        MockResponse::make(['message' => 'Server Error'], 500),
    ]);

    $promise = TestConnector::make()->sendAsync(new ErrorRequest, $mockClient);

    try {
        $promise->wait();
    }  catch (Throwable $exception) {
        expect($exception)->toBeInstanceOf(RequestException::class);
        expect($exception->getResponse())->toBeInstanceOf(Response::class);
        expect($exception->getMessage())->toEqual($exception->getResponse()->body());
        expect($exception->getPrevious())->toBeNull();
    }
});

test('it throws different exceptions based for common status codes', function () {
    //
});

test('you can customise the exception handler on a connector', function () {
    //
});

test('you can customise the exception handler on a request', function () {
    //
});

test('the request exception handler will always take priority', function () {
    //
});

test('you can customise if saloon should throw an exception', function () {
    //
});

test('the sender exception can be null', function () {
    //
});
