<?php

declare(strict_types=1);

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Requests\ErrorRequest;
use Saloon\Exceptions\NoMockResponseFoundException;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Exceptions\TestResponseException;
use Saloon\Tests\Fixtures\Connectors\QueryParameterConnector;
use Saloon\Tests\Fixtures\Connectors\DifferentServiceConnector;
use Saloon\Tests\Fixtures\Requests\DifferentServiceUserRequest;
use Saloon\Tests\Fixtures\Requests\QueryParameterConnectorRequest;

test('you can create sequence mocks', function () {
    $responseA = MockResponse::make(['name' => 'Sammyjo20']);
    $responseB = MockResponse::make(['name' => 'Alex']);

    $mockClient = new MockClient([$responseA, $responseB]);

    expect($mockClient->getNextFromSequence())->toEqual($responseA);
    expect($mockClient->getNextFromSequence())->toEqual($responseB);
    expect($mockClient->isEmpty())->toBeTrue();
});

test('you can create connector mocks', function () {
    $responseA = MockResponse::make(['name' => 'Sammyjo20']);
    $responseB = MockResponse::make(['name' => 'Alex']);

    $connectorA = new TestConnector;
    $connectorB = new QueryParameterConnector;

    $connectorARequest = new UserRequest;
    $connectorBRequest = new QueryParameterConnectorRequest;

    $mockClient = new MockClient([
        TestConnector::class => $responseA,
        QueryParameterConnector::class => $responseB,
    ]);

    expect($mockClient->guessNextResponse($connectorA->createPendingRequest($connectorARequest)))->toEqual($responseA);
    expect($mockClient->guessNextResponse($connectorB->createPendingRequest($connectorBRequest)))->toEqual($responseB);
    expect($mockClient->isEmpty())->toBeFalse();
});

test('you can create request mocks', function () {
    $responseA = MockResponse::make(['name' => 'Sammyjo20']);
    $responseB = MockResponse::make(['name' => 'Alex']);

    $connectorA = new TestConnector;
    $connectorB = new QueryParameterConnector;

    $requestA = new UserRequest;
    $requestB = new QueryParameterConnectorRequest;

    $mockClient = new MockClient([
        UserRequest::class => $responseA,
        QueryParameterConnectorRequest::class => $responseB,
    ]);

    expect($mockClient->guessNextResponse($connectorA->createPendingRequest($requestA)))->toEqual($responseA);
    expect($mockClient->guessNextResponse($connectorB->createPendingRequest($requestB)))->toEqual($responseB);
    expect($mockClient->isEmpty())->toBeFalse();
});

test('you can create url mocks', function () {
    $responseA = MockResponse::make(['name' => 'Sammyjo20']);
    $responseB = MockResponse::make(['name' => 'Alex']);
    $responseC = MockResponse::make(['name' => 'Sam Carré']);

    $connectorA = new TestConnector;
    $connectorB = new DifferentServiceConnector;

    $requestA = new UserRequest;
    $requestB = new ErrorRequest;
    $requestC = new DifferentServiceUserRequest;

    $mockClient = new MockClient([
        'tests.saloon.dev/api/user' => $responseA, // Test Exact Route
        'tests.saloon.dev/*' => $responseB, // Test Wildcard Routes
        'google.com/*' => $responseC, // Test Different Route,
    ]);

    expect($mockClient->guessNextResponse($connectorA->createPendingRequest($requestA)))->toEqual($responseA);
    expect($mockClient->guessNextResponse($connectorA->createPendingRequest($requestB)))->toEqual($responseB);
    expect($mockClient->guessNextResponse($connectorB->createPendingRequest($requestC)))->toEqual($responseC);
});

test('you can create wildcard url mocks', function () {
    $responseA = MockResponse::make(['name' => 'Sammyjo20']);
    $responseB = MockResponse::make(['name' => 'Alex']);
    $responseC = MockResponse::make(['name' => 'Sam Carré']);

    $connectorA = new TestConnector;
    $connectorB = new DifferentServiceConnector;

    $requestA = new UserRequest;
    $requestB = new ErrorRequest;
    $requestC = new DifferentServiceUserRequest;

    $mockClient = new MockClient([
        'tests.saloon.dev/api/user' => $responseA, // Test Exact Route
        'tests.saloon.dev/*' => $responseB, // Test Wildcard Routes
        '*' => $responseC,
    ]);

    expect($mockClient->guessNextResponse($connectorA->createPendingRequest($requestA)))->toEqual($responseA);
    expect($mockClient->guessNextResponse($connectorA->createPendingRequest($requestB)))->toEqual($responseB);
    expect($mockClient->guessNextResponse($connectorB->createPendingRequest($requestC)))->toEqual($responseC);
});

test('saloon throws an exception if it cant work out the url response', function () {
    $responseA = MockResponse::make(['name' => 'Sammyjo20']);
    $responseB = MockResponse::make(['name' => 'Alex']);
    $responseC = MockResponse::make(['name' => 'Sam Carré']);

    $connectorA = new TestConnector;
    $connectorB = new DifferentServiceConnector;

    $requestA = new UserRequest;
    $requestB = new ErrorRequest;
    $requestC = new DifferentServiceUserRequest;

    $mockClient = new MockClient([
        'tests.saloon.dev/api/user' => $responseA, // Test Exact Route
        'tests.saloon.dev/*' => $responseB, // Test Wildcard Routes
    ]);

    expect($mockClient->guessNextResponse($connectorA->createPendingRequest($requestA)))->toEqual($responseA);
    expect($mockClient->guessNextResponse($connectorA->createPendingRequest($requestB)))->toEqual($responseB);

    $this->expectException(NoMockResponseFoundException::class);
    $this->expectExceptionMessage('Saloon was unable to guess a mock response for your request [https://google.com/user], consider using a wildcard url mock or a connector mock.');

    expect($mockClient->guessNextResponse($connectorB->createPendingRequest($requestC)))->toEqual($responseC);
});

test('you can get an array of the recorded requests', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam']),
        MockResponse::make(['name' => 'Taylor']),
        MockResponse::make(['name' => 'Marcel']),
    ]);

    $connector = new TestConnector;

    $responseA = $connector->send(new UserRequest, $mockClient);
    $responseB = $connector->send(new UserRequest, $mockClient);
    $responseC = $connector->send(new UserRequest, $mockClient);

    $responses = $mockClient->getRecordedResponses();

    expect($responses)->toEqual([
        $responseA,
        $responseB,
        $responseC,
    ]);
});

test('you can get the last recorded request', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam']),
        MockResponse::make(['name' => 'Taylor']),
        MockResponse::make(['name' => 'Marcel']),
    ]);

    $connector = new TestConnector;

    $responseA = $connector->send(new UserRequest, $mockClient);
    $responseB = $connector->send(new UserRequest, $mockClient);
    $responseC = $connector->send(new UserRequest, $mockClient);

    $lastResponse = $mockClient->getLastResponse();

    expect($lastResponse)->toBe($responseC);
});

test('if there are no recorded responses the getLastResponse will return null', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam']),
    ]);

    expect($mockClient)->getLastResponse()->toBeNull();
});

test('if there are no recorded responses the getLastRequest will return null', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam']),
    ]);

    expect($mockClient)->getLastRequest()->toBeNull();
});

test('if the response is not the last response it will use the loop to find it', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam']),
        MockResponse::make(['error' => 'Server Error'], 500),
    ]);

    $responseA = connector()->send(new ErrorRequest, $mockClient);
    $responseB = connector()->send(new UserRequest, $mockClient);

    expect($mockClient)->getLastResponse()->toBe($responseB);

    // Uses last response

    expect($mockClient)->findResponseByRequest(UserRequest::class)->toBe($responseB);

    // Does not use the last response

    expect($mockClient)->findResponseByRequest(ErrorRequest::class)->toBe($responseA);
});

test('it will find the response by url if it is not the last response', function () {
    $mockClient = new MockClient([
        '/user' => MockResponse::make(['name' => 'Sam']),
        '/error' => MockResponse::make(['error' => 'Server Error'], 500),
    ]);

    $responseA = connector()->send(new ErrorRequest, $mockClient);
    $responseB = connector()->send(new UserRequest, $mockClient);

    expect($mockClient)->getLastResponse()->toBe($responseB);

    // Uses last response

    expect($mockClient)->findResponseByRequestUrl('/user')->toBe($responseB);

    // Does not use the last response

    expect($mockClient)->findResponseByRequestUrl('/error')->toBe($responseA);
});

test('you can mock exceptions with a closure', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam']),
        MockResponse::make(['name' => 'Patrick'])->throw(fn ($pendingRequest) => new TestResponseException('Unable to connect!', $pendingRequest)),
    ]);

    $okResponse = connector()->send(new UserRequest, $mockClient);

    expect($okResponse->json())->toEqual(['name' => 'Sam']);

    $this->expectException(TestResponseException::class);
    $this->expectExceptionMessage('Unable to connect!');

    $response = connector()->send(new UserRequest, $mockClient);
    $response->throw();
});

test('you can mock normal exceptions', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Michael'])->throw(new Exception('Custom Exception!')),
    ]);

    $this->expectException(Exception::class);
    $this->expectExceptionMessage('Custom Exception!');

    $response = connector()->send(new UserRequest, $mockClient);
    $response->throw();
});
