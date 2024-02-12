<?php

declare(strict_types=1);

use Saloon\Http\Response;
use Saloon\Http\PendingRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Exception\ServerException;
use Saloon\Exceptions\Request\ClientException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Requests\ErrorRequest;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Tests\Fixtures\Requests\BadResponseRequest;
use Saloon\Tests\Fixtures\Requests\NotFoundFailedRequest;
use Saloon\Tests\Fixtures\Connectors\BadResponseConnector;
use Saloon\Tests\Fixtures\Exceptions\CustomRequestException;
use Saloon\Tests\Fixtures\Requests\CustomFailHandlerRequest;
use Saloon\Tests\Fixtures\Connectors\CustomExceptionConnector;
use Saloon\Tests\Fixtures\Requests\CustomExceptionUserRequest;
use Saloon\Tests\Fixtures\Exceptions\ConnectorRequestException;
use Saloon\Tests\Fixtures\Connectors\CustomFailHandlerConnector;
use Saloon\Exceptions\Request\Statuses\InternalServerErrorException;
use Saloon\Exceptions\Request\ServerException as SaloonServerException;

test('you can use the to exception method to get the default RequestException exception with GuzzleSender', function () {
    $response = TestConnector::make()->send(new ErrorRequest);

    expect($response)->toBeInstanceOf(Response::class);

    $exception = $response->toException();

    expect($exception)->toBeInstanceOf(InternalServerErrorException::class);
    expect($exception)->toBeInstanceOf(SaloonServerException::class);
    expect($exception->getMessage())->toEqual('Internal Server Error (500) Response: ' . $response->body());
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

    expect($exception)->toBeInstanceOf(InternalServerErrorException::class);
    expect($exception)->toBeInstanceOf(SaloonServerException::class);
    expect($exception->getMessage())->toEqual('Internal Server Error (500) Response: ' . $response->body());

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
    } catch (Throwable $exception) {
        expect($correctInstance)->toBeTrue();
        expect($exception)->toBeInstanceOf(RequestException::class);
        expect($exception->getResponse())->toBeInstanceOf(Response::class);
        expect($exception->getMessage())->toEqual('Internal Server Error (500) Response: ' . $exception->getResponse()->body());
        expect($exception->getPrevious())->toBeInstanceOf(ServerException::class);
    }
});

test('it throws exceptions properly with promises', function () {
    $mockClient = new MockClient([
        MockResponse::make(['message' => 'Bad Request'], 422),
    ]);

    $promise = TestConnector::make()->sendAsync(new ErrorRequest, $mockClient);

    try {
        $promise->wait();
    } catch (Throwable $exception) {
        expect($exception)->toBeInstanceOf(ClientException::class);
        expect($exception->getResponse())->toBeInstanceOf(Response::class);
        expect($exception->getMessage())->toEqual('Unprocessable Entity (422) Response: ' . $exception->getResponse()->body());
        expect($exception->getPrevious())->toBeNull();
    }
});

test('you can customise the exception handler on a connector', function () {
    $mockClient = new MockClient([
        MockResponse::make(['message' => 'Server Error'], 500),
    ]);

    $response = CustomExceptionConnector::make()->send(new UserRequest, $mockClient);
    $exception = $response->toException();

    expect($exception)->toBeInstanceOf(ConnectorRequestException::class);
    expect($exception->getMessage())->toEqual('Oh yee-naw.');
});

test('you can customise the exception handler on a request', function () {
    $mockClient = new MockClient([
        MockResponse::make(['message' => 'Server Error'], 500),
    ]);

    $response = TestConnector::make()->send(new CustomExceptionUserRequest, $mockClient);
    $exception = $response->toException();

    expect($exception)->toBeInstanceOf(CustomRequestException::class);
    expect($exception->getMessage())->toEqual('Oh yee-naw.');
});

test('the request exception handler will always take priority', function () {
    $mockClient = new MockClient([
        MockResponse::make(['message' => 'Server Error'], 500),
    ]);

    $response = CustomExceptionConnector::make()->send(new CustomExceptionUserRequest, $mockClient);
    $exception = $response->toException();

    expect($exception)->toBeInstanceOf(CustomRequestException::class);
    expect($exception->getMessage())->toEqual('Oh yee-naw.');
});

test('you can customise if saloon should throw an exception on a connector', function () {
    $mockClient = new MockClient([
        MockResponse::make(['message' => 'Success']),
        MockResponse::make(['message' => 'Error: Invalid Cowboy Hat']),
    ]);

    $responseA = BadResponseConnector::make()->send(new UserRequest, $mockClient);

    expect($responseA->shouldThrowRequestException())->toBeFalse();
    expect($responseA->toException())->toBeNull();

    $responseB = BadResponseConnector::make()->send(new UserRequest, $mockClient);
    expect($responseB->shouldThrowRequestException())->toBeTrue();
    $exceptionB = $responseB->toException();

    expect($exceptionB)->toBeInstanceOf(RequestException::class);
    expect($exceptionB->getPendingRequest())->toBeInstanceOf(PendingRequest::class);
    expect($exceptionB->getResponse())->toBeInstanceOf(Response::class);
    expect($exceptionB->getMessage())->toEqual('OK (200) Response: ' . $exceptionB->getResponse()->body());
    expect($exceptionB->getPrevious())->toBeNull();
});

test('you can customise if saloon should throw an exception on a request', function () {
    $mockClient = new MockClient([
        MockResponse::make(['message' => 'Success']),
        MockResponse::make(['message' => 'Yee-naw: Horse Not Found']),
    ]);

    $responseA = TestConnector::make()->send(new BadResponseRequest, $mockClient);

    expect($responseA->shouldThrowRequestException())->toBeFalse();
    expect($responseA->toException())->toBeNull();

    $responseB = TestConnector::make()->send(new BadResponseRequest, $mockClient);
    expect($responseB->shouldThrowRequestException())->toBeTrue();
    $exceptionB = $responseB->toException();

    expect($exceptionB)->toBeInstanceOf(RequestException::class);
    expect($exceptionB->getPendingRequest())->toBeInstanceOf(PendingRequest::class);
    expect($exceptionB->getResponse())->toBeInstanceOf(Response::class);
    expect($exceptionB->getMessage())->toEqual('OK (200) Response: ' . $exceptionB->getResponse()->body());
    expect($exceptionB->getPrevious())->toBeNull();
});

test('when both the connector and request have custom logic to determine different failures they work together', function () {
    $mockClient = new MockClient([
        MockResponse::make(['message' => 'Success']),
        MockResponse::make(['message' => 'Error: Invalid Cowboy Hat']),
        MockResponse::make(['message' => 'Yee-naw: Horse Not Found']),
    ]);

    $responseA = BadResponseConnector::make()->send(new BadResponseRequest, $mockClient);

    expect($responseA->shouldThrowRequestException())->toBeFalse();
    expect($responseA->toException())->toBeNull();

    $responseB = BadResponseConnector::make()->send(new BadResponseRequest, $mockClient);
    expect($responseB->shouldThrowRequestException())->toBeTrue();
    $exceptionB = $responseB->toException();

    expect($exceptionB)->toBeInstanceOf(RequestException::class);
    expect($exceptionB->getPendingRequest())->toBeInstanceOf(PendingRequest::class);
    expect($exceptionB->getResponse())->toBeInstanceOf(Response::class);
    expect($exceptionB->getMessage())->toEqual('OK (200) Response: ' . $exceptionB->getResponse()->body());
    expect($exceptionB->getPrevious())->toBeNull();

    $responseC = BadResponseConnector::make()->send(new BadResponseRequest, $mockClient);
    expect($responseC->shouldThrowRequestException())->toBeTrue();
    $exceptionC = $responseC->toException();

    expect($exceptionC)->toBeInstanceOf(RequestException::class);
    expect($exceptionC->getResponse())->toBeInstanceOf(Response::class);
    expect($exceptionC->getMessage())->toEqual('OK (200) Response: ' . $exceptionC->getResponse()->body());
    expect($exceptionC->getPrevious())->toBeNull();
});

test('you can customise if saloon determines if a request has failed on a connector', function () {
    $mockClient = new MockClient([
        MockResponse::make(['message' => 'Success']),
        MockResponse::make(['message' => 'Error: Invalid Cowboy Hat']),
    ]);

    $responseA = CustomFailHandlerConnector::make()->send(new UserRequest, $mockClient);

    expect($responseA->failed())->toBeFalse();

    $responseB = CustomFailHandlerConnector::make()->send(new UserRequest, $mockClient);

    expect($responseB->failed())->toBeTrue();
});

test('you can customise if saloon determines if a request has failed on a request', function () {
    $mockClient = new MockClient([
        MockResponse::make(['message' => 'Success']),
        MockResponse::make(['message' => 'Yee-naw: Horse Not Found']),
    ]);

    $responseA = TestConnector::make()->send(new CustomFailHandlerRequest, $mockClient);

    expect($responseA->failed())->toBeFalse();

    $responseB = TestConnector::make()->send(new CustomFailHandlerRequest, $mockClient);

    expect($responseB->failed())->toBeTrue();
});

test('a request can mark a request as not failed', function () {
    $response = TestConnector::make()->send(new NotFoundFailedRequest);

    expect($response->failed())->toBeFalse();
});

test('a request can mark a request as not failed with asynchronous requests', function () {
    $response = TestConnector::make()->sendAsync(new NotFoundFailedRequest)->wait();

    expect($response->failed())->toBeFalse();
});

test('a request can mark a request as not failed with pools', function () {
    $responseCount = 0;
    $exceptionCount = 0;

    $pool = TestConnector::make()->pool([
        new NotFoundFailedRequest,
    ]);

    $pool->withResponseHandler(function (Response $response) use (&$responseCount) {
        expect($response)->toBeInstanceOf(Response::class);
        expect($response->status())->toBe(404);

        $responseCount++;
    })->withExceptionHandler(function (RequestException $exception) use (&$exceptionCount) {
        $response = $exception->getResponse();

        expect($response)->toBeInstanceOf(Response::class);
        expect($response->status())->toBe(404);

        $exceptionCount++;
    });

    $promise = $pool->send();

    expect($promise)->toBeInstanceOf(PromiseInterface::class);

    $promise->wait();

    expect($responseCount)->toEqual(1);
    expect($exceptionCount)->toEqual(0);
});

test('the sender will throw a FatalRequestException if it cannot connect to a site using synchronous', function (string $url) {
    $connector = new TestConnector($url);
    $request = new UserRequest();

    $this->expectException(FatalRequestException::class);

    $connector->send($request);
})->with([
    'https://saloon.saloon.test',
    'https://saloon.doesnt-exist',
]);

test('the sender will throw a FatalRequestException if it cannot connect to a site using asynchronous', function (string $url) {
    $connector = new TestConnector($url);
    $request = new UserRequest();

    $this->expectException(FatalRequestException::class);

    $connector->sendAsync($request)->wait();
})->with([
    'https://saloon.saloon.test',
    'https://saloon.doesnt-exist',
]);
