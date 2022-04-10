<?php

use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\UserRequest;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\ErrorRequest;
use Sammyjo20\Saloon\Tests\Fixtures\Connectors\TestConnector;
use Sammyjo20\Saloon\Exceptions\SaloonNoMockResponseFoundException;
use Sammyjo20\Saloon\Tests\Fixtures\Connectors\QueryParameterConnector;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\DifferentServiceUserRequest;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\QueryParameterConnectorRequest;

test('you can create sequence mocks', function () {
    $responseA = MockResponse::make(['name' => 'Sammyjo20'], 200);
    $responseB = MockResponse::make(['name' => 'Alex'], 200);

    $mockClient = new MockClient([$responseA, $responseB]);

    expect($mockClient->getNextFromSequence())->toEqual($responseA);
    expect($mockClient->getNextFromSequence())->toEqual($responseB);
    expect($mockClient->isEmpty())->toBeTrue();
});

test('you can create connector mocks', function () {
    $responseA = MockResponse::make(['name' => 'Sammyjo20'], 200);
    $responseB = MockResponse::make(['name' => 'Alex'], 200);

    $connectorARequest = new UserRequest;
    $connectorBRequest = new QueryParameterConnectorRequest;

    $mockClient = new MockClient([
        TestConnector::class => $responseA,
        QueryParameterConnector::class => $responseB,
    ]);

    expect($mockClient->guessNextResponse($connectorARequest))->toEqual($responseA);
    expect($mockClient->guessNextResponse($connectorBRequest))->toEqual($responseB);
    expect($mockClient->isEmpty())->toBeFalse();
});

test('you can create request mocks', function () {
    $responseA = MockResponse::make(['name' => 'Sammyjo20'], 200);
    $responseB = MockResponse::make(['name' => 'Alex'], 200);

    $requestA = new UserRequest;
    $requestB = new QueryParameterConnectorRequest;

    $mockClient = new MockClient([
        UserRequest::class => $responseA,
        QueryParameterConnectorRequest::class => $responseB,
    ]);

    expect($mockClient->guessNextResponse($requestA))->toEqual($responseA);
    expect($mockClient->guessNextResponse($requestB))->toEqual($responseB);
    expect($mockClient->isEmpty())->toBeFalse();
});

test('you can create url mocks', function () {
    $responseA = MockResponse::make(['name' => 'Sammyjo20'], 200);
    $responseB = MockResponse::make(['name' => 'Alex'], 200);
    $responseC = MockResponse::make(['name' => 'Sam Carré'], 200);

    $requestA = new UserRequest;
    $requestB = new ErrorRequest;
    $requestC = new DifferentServiceUserRequest;

    $mockClient = new MockClient([
        'tests.saloon.dev/api/user' => $responseA, // Test Exact Route
        'tests.saloon.dev/*' => $responseB, // Test Wildcard Routes
        'google.com/*' => $responseC, // Test Different Route,
    ]);

    expect($mockClient->guessNextResponse($requestA))->toEqual($responseA);
    expect($mockClient->guessNextResponse($requestB))->toEqual($responseB);
    expect($mockClient->guessNextResponse($requestC))->toEqual($responseC);
});

test('you can create wildcard url mocks', function () {
    $responseA = MockResponse::make(['name' => 'Sammyjo20'], 200);
    $responseB = MockResponse::make(['name' => 'Alex'], 200);
    $responseC = MockResponse::make(['name' => 'Sam Carré'], 200);

    $requestA = new UserRequest;
    $requestB = new ErrorRequest;
    $requestC = new DifferentServiceUserRequest;

    $mockClient = new MockClient([
        'tests.saloon.dev/api/user' => $responseA, // Test Exact Route
        'tests.saloon.dev/*' => $responseB, // Test Wildcard Routes
        '*' => $responseC,
    ]);

    expect($mockClient->guessNextResponse($requestA))->toEqual($responseA);
    expect($mockClient->guessNextResponse($requestB))->toEqual($responseB);
    expect($mockClient->guessNextResponse($requestC))->toEqual($responseC);
});

test('saloon throws an exception if it cant work out the url response', function () {
    $responseA = MockResponse::make(['name' => 'Sammyjo20'], 200);
    $responseB = MockResponse::make(['name' => 'Alex'], 200);
    $responseC = MockResponse::make(['name' => 'Sam Carré'], 200);

    $requestA = new UserRequest;
    $requestB = new ErrorRequest;
    $requestC = new DifferentServiceUserRequest;

    $mockClient = new MockClient([
        'tests.saloon.dev/api/user' => $responseA, // Test Exact Route
        'tests.saloon.dev/*' => $responseB, // Test Wildcard Routes
    ]);

    expect($mockClient->guessNextResponse($requestA))->toEqual($responseA);
    expect($mockClient->guessNextResponse($requestB))->toEqual($responseB);

    $this->expectException(SaloonNoMockResponseFoundException::class);

    expect($mockClient->guessNextResponse($requestC))->toEqual($responseC);
});

test('you can get an array of the recorded requests', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam'], 200),
        MockResponse::make(['name' => 'Taylor'], 200),
        MockResponse::make(['name' => 'Marcel'], 200),
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
        MockResponse::make(['name' => 'Sam'], 200),
        MockResponse::make(['name' => 'Taylor'], 200),
        MockResponse::make(['name' => 'Marcel'], 200),
    ]);

    $responseA = (new UserRequest())->send($mockClient);
    $responseB = (new UserRequest())->send($mockClient);
    $responseC = (new UserRequest())->send($mockClient);

    $lastResponse = $mockClient->getLastResponse();

    expect($lastResponse)->toBe($responseC);
});

test('if there are no recorded responses the getLastResponse will return null', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam'], 200),
    ]);

    expect($mockClient)->getLastResponse()->toBeNull();
});

test('if there are no recorded responses the getLastRequest will return null', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam'], 200),
    ]);

    expect($mockClient)->getLastRequest()->toBeNull();
});

test('if the response is not the last response it will use the loop to find it', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam'], 200),
        MockResponse::make(['error' => 'Server Error'], 500),
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
        '/user' => MockResponse::make(['name' => 'Sam'], 200),
        '/error' => MockResponse::make(['error' => 'Server Error'], 500),
    ]);

    $responseA = (new ErrorRequest())->send($mockClient);
    $responseB = (new UserRequest())->send($mockClient);

    expect($mockClient)->getLastResponse()->toBe($responseB);

    // Uses last response

    expect($mockClient)->findResponseByRequestUrl('/user')->toBe($responseB);

    // Does not use the last response

    expect($mockClient)->findResponseByRequestUrl('/error')->toBe($responseA);
});
