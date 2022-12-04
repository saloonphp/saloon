<?php

declare(strict_types=1);

use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Promise\PromiseInterface;
use Saloon\Contracts\Response as ResponseContract;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Exceptions\Request\RequestException;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\PendingRequest;
use Saloon\Http\Responses\Response;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Requests\ErrorRequest;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Connectors\InvalidConnectionConnector;

test('you can create a pool on a connector', function () {
    $connector = new TestConnector;
    $successCount = 0;
    $errorCount = 0;

    $pool = $connector->pool([
        new UserRequest,
        new UserRequest,
        new UserRequest,
        new UserRequest,
        new UserRequest,
        new ErrorRequest,
    ]);

    $pool->setConcurrency(6);

    $pool->withResponseHandler(function (ResponseContract $response) use (&$successCount) {
        expect($response)->toBeInstanceOf(Response::class);
        expect($response->json())->toEqual([
            'name' => 'Sammyjo20',
            'actual_name' => 'Sam',
            'twitter' => '@carre_sam',
        ]);

        $successCount++;
    });

    $pool->withExceptionHandler(function (RequestException $exception) use (&$errorCount) {
        $response = $exception->getResponse();

        expect($response)->toBeInstanceOf(Response::class);

        $errorCount++;
    });

    $promise = $pool->send();

    expect($promise)->toBeInstanceOf(PromiseInterface::class);

    $promise->wait();

    expect($successCount)->toEqual(5);
    expect($errorCount)->toEqual(1);
});

test('if a pool has a request that cannot connect it will be caught in the handleException callback', function () {
    $connector = new InvalidConnectionConnector;
    $count = 0;

    $pool = $connector->pool([
        new UserRequest,
        new UserRequest,
        new UserRequest,
        new UserRequest,
        new UserRequest,
    ]);

    $pool->setConcurrency(5);

    $pool->withExceptionHandler(function (FatalRequestException $ex) use (&$count) {
        expect($ex)->toBeInstanceOf(FatalRequestException::class);
        expect($ex->getPrevious())->toBeInstanceOf(ConnectException::class);
        expect($ex->getPendingRequest())->toBeInstanceOf(PendingRequest::class);

        $count++;
    });

    $promise = $pool->send();

    $promise->wait();

    expect($count)->toEqual(5);
});

test('you can use pool with a mock client added and it wont send real requests', function () {
    $mockResponses = [
        MockResponse::make(['name' => 'Sam']),
        MockResponse::make(['name' => 'Charlotte']),
        MockResponse::make(['name' => 'Mantas']),
        MockResponse::make(['name' => 'Emily']),
        MockResponse::make(['name' => 'Error'], 500),
    ];

    $mockClient = new MockClient($mockResponses);

    $connector = new TestConnector;
    $connector->withMockClient($mockClient);
    $successCount = 0;
    $errorCount = 0;

    $pool = $connector->pool([
        new UserRequest,
        new UserRequest,
        new UserRequest,
        new UserRequest,
        new ErrorRequest,
    ]);

    $pool->setConcurrency(6);

    $pool->withResponseHandler(function (ResponseContract $response) use (&$successCount, $mockResponses) {
        expect($response)->toBeInstanceOf(Response::class);
        expect($response->json())->toEqual($mockResponses[$successCount]->getBody()->all());

        $successCount++;
    });

    $pool->withExceptionHandler(function (RequestException $exception) use (&$errorCount) {
        $response = $exception->getResponse();

        expect($response)->toBeInstanceOf(Response::class);
        expect($response->json())->toEqual(['name' => 'Error']);

        $errorCount++;
    });

    $promise = $pool->send();

    $promise->wait();

    expect($successCount)->toEqual(4);
    expect($errorCount)->toEqual(1);
});
