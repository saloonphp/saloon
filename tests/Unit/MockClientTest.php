<?php

declare(strict_types=1);

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use GuzzleHttp\Exception\ConnectException;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Requests\ErrorRequest;
use Saloon\Exceptions\NoMockResponseFoundException;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Connectors\QueryParameterConnector;
use Saloon\Tests\Fixtures\Requests\DifferentServiceUserRequest;
use Saloon\Tests\Fixtures\Requests\QueryParameterConnectorRequest;

test('you can create sequence mocks', function () {
    $responseA = MockResponse::make(200, ['name' => 'Sammyjo20']);
    $responseB = MockResponse::make(200, ['name' => 'Alex']);

    $mockClient = new MockClient([$responseA, $responseB]);

    expect($mockClient->getNextFromSequence())->toEqual($responseA);
    expect($mockClient->getNextFromSequence())->toEqual($responseB);
    expect($mockClient->isEmpty())->toBeTrue();
});

test('you can create connector mocks', function () {
    $responseA = MockResponse::make(200, ['name' => 'Sammyjo20']);
    $responseB = MockResponse::make(200, ['name' => 'Alex']);

    $connectorARequest = new UserRequest;
    $connectorBRequest = new QueryParameterConnectorRequest;

    $mockClient = new MockClient([
        TestConnector::class => $responseA,
        QueryParameterConnector::class => $responseB,
    ]);

    expect($mockClient->guessNextResponse($connectorARequest->createPendingRequest()))->toEqual($responseA);
    expect($mockClient->guessNextResponse($connectorBRequest->createPendingRequest()))->toEqual($responseB);
    expect($mockClient->isEmpty())->toBeFalse();
});

test('you can create request mocks', function () {
    $responseA = MockResponse::make(200, ['name' => 'Sammyjo20']);
    $responseB = MockResponse::make(200, ['name' => 'Alex']);

    $requestA = new UserRequest;
    $requestB = new QueryParameterConnectorRequest;

    $mockClient = new MockClient([
        UserRequest::class => $responseA,
        QueryParameterConnectorRequest::class => $responseB,
    ]);

    expect($mockClient->guessNextResponse($requestA->createPendingRequest()))->toEqual($responseA);
    expect($mockClient->guessNextResponse($requestB->createPendingRequest()))->toEqual($responseB);
    expect($mockClient->isEmpty())->toBeFalse();
});

test('you can create url mocks', function () {
    $responseA = MockResponse::make(200, ['name' => 'Sammyjo20']);
    $responseB = MockResponse::make(200, ['name' => 'Alex']);
    $responseC = MockResponse::make(200, ['name' => 'Sam Carré']);

    $requestA = new UserRequest;
    $requestB = new ErrorRequest;
    $requestC = new DifferentServiceUserRequest;

    $mockClient = new MockClient([
        'tests.saloon.dev/api/user' => $responseA, // Test Exact Route
        'tests.saloon.dev/*' => $responseB, // Test Wildcard Routes
        'google.com/*' => $responseC, // Test Different Route,
    ]);

    expect($mockClient->guessNextResponse($requestA->createPendingRequest()))->toEqual($responseA);
    expect($mockClient->guessNextResponse($requestB->createPendingRequest()))->toEqual($responseB);
    expect($mockClient->guessNextResponse($requestC->createPendingRequest()))->toEqual($responseC);
});

test('you can create wildcard url mocks', function () {
    $responseA = MockResponse::make(200, ['name' => 'Sammyjo20']);
    $responseB = MockResponse::make(200, ['name' => 'Alex']);
    $responseC = MockResponse::make(200, ['name' => 'Sam Carré']);

    $requestA = new UserRequest;
    $requestB = new ErrorRequest;
    $requestC = new DifferentServiceUserRequest;

    $mockClient = new MockClient([
        'tests.saloon.dev/api/user' => $responseA, // Test Exact Route
        'tests.saloon.dev/*' => $responseB, // Test Wildcard Routes
        '*' => $responseC,
    ]);

    expect($mockClient->guessNextResponse($requestA->createPendingRequest()))->toEqual($responseA);
    expect($mockClient->guessNextResponse($requestB->createPendingRequest()))->toEqual($responseB);
    expect($mockClient->guessNextResponse($requestC->createPendingRequest()))->toEqual($responseC);
});

test('saloon throws an exception if it cant work out the url response', function () {
    $responseA = MockResponse::make(200, ['name' => 'Sammyjo20']);
    $responseB = MockResponse::make(200, ['name' => 'Alex']);
    $responseC = MockResponse::make(200, ['name' => 'Sam Carré']);

    $requestA = new UserRequest;
    $requestB = new ErrorRequest;
    $requestC = new DifferentServiceUserRequest;

    $mockClient = new MockClient([
        'tests.saloon.dev/api/user' => $responseA, // Test Exact Route
        'tests.saloon.dev/*' => $responseB, // Test Wildcard Routes
    ]);

    expect($mockClient->guessNextResponse($requestA->createPendingRequest()))->toEqual($responseA);
    expect($mockClient->guessNextResponse($requestB->createPendingRequest()))->toEqual($responseB);

    $this->expectException(NoMockResponseFoundException::class);

    expect($mockClient->guessNextResponse($requestC->createPendingRequest()))->toEqual($responseC);
});

test('you can get an array of the recorded requests', function () {
    $mockClient = new MockClient([
        MockResponse::make(200, ['name' => 'Sam']),
        MockResponse::make(200, ['name' => 'Taylor']),
        MockResponse::make(200, ['name' => 'Marcel']),
    ]);

    $responseA = (new UserRequest())->send($mockClient);
    $responseB = (new UserRequest())->send($mockClient);
    $responseC = (new UserRequest())->send($mockClient);

    $responses = $mockClient->getRecordedResponses();

    expect($responses)->toEqual([
        $responseA,
        $responseB,
        $responseC,
    ]);
});

test('you can get the last recorded request', function () {
    $mockClient = new MockClient([
        MockResponse::make(200, ['name' => 'Sam']),
        MockResponse::make(200, ['name' => 'Taylor']),
        MockResponse::make(200, ['name' => 'Marcel']),
    ]);

    $responseA = (new UserRequest())->send($mockClient);
    $responseB = (new UserRequest())->send($mockClient);
    $responseC = (new UserRequest())->send($mockClient);

    $lastResponse = $mockClient->getLastResponse();

    expect($lastResponse)->toBe($responseC);
});

test('if there are no recorded responses the getLastResponse will return null', function () {
    $mockClient = new MockClient([
        MockResponse::make(200, ['name' => 'Sam']),
    ]);

    expect($mockClient)->getLastResponse()->toBeNull();
});

test('if there are no recorded responses the getLastRequest will return null', function () {
    $mockClient = new MockClient([
        MockResponse::make(200, ['name' => 'Sam']),
    ]);

    expect($mockClient)->getLastRequest()->toBeNull();
});

test('if the response is not the last response it will use the loop to find it', function () {
    $mockClient = new MockClient([
        MockResponse::make(200, ['name' => 'Sam']),
        MockResponse::make(500, ['error' => 'Server Error']),
    ]);

    $responseA = (new ErrorRequest())->send($mockClient);
    $responseB = (new UserRequest())->send($mockClient);

    expect($mockClient)->getLastResponse()->toBe($responseB);

    // Uses last response

    expect($mockClient)->findResponseByRequest(UserRequest::class)->toBe($responseB);

    // Does not use the last response

    expect($mockClient)->findResponseByRequest(ErrorRequest::class)->toBe($responseA);
});

test('it will find the response by url if it is not the last response', function () {
    $mockClient = new MockClient([
        '/user' => MockResponse::make(200, ['name' => 'Sam']),
        '/error' => MockResponse::make(500, ['error' => 'Server Error']),
    ]);

    $responseA = (new ErrorRequest())->send($mockClient);
    $responseB = (new UserRequest())->send($mockClient);

    expect($mockClient)->getLastResponse()->toBe($responseB);

    // Uses last response

    expect($mockClient)->findResponseByRequestUrl('/user')->toBe($responseB);

    // Does not use the last response

    expect($mockClient)->findResponseByRequestUrl('/error')->toBe($responseA);
});

test('you can mock guzzle exceptions', function () {
    $mockClient = new MockClient([
        MockResponse::make(200, ['name' => 'Sam']),
        MockResponse::make(200, ['name' => 'Patrick'])->throw(fn ($guzzleRequest) => new ConnectException('Unable to connect!')),
    ]);

    $okResponse = (new UserRequest())->send($mockClient);

    expect($okResponse->json())->toEqual(['name' => 'Sam']);

    $this->expectException(ConnectException::class);
    $this->expectExceptionMessage('Unable to connect!');

    (new UserRequest())->send($mockClient);
});

test('you can mock normal exceptions', function () {
    $mockClient = new MockClient([
        MockResponse::make(200, ['name' => 'Michael'])->throw(new Exception('Custom Exception!')),
    ]);

    $this->expectException(Exception::class);
    $this->expectExceptionMessage('Custom Exception!');

    (new UserRequest())->send($mockClient);
});
