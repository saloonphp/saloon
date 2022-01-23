<?php

use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Tests\Resources\Requests\UserRequest;
use Sammyjo20\Saloon\Tests\Resources\Requests\ErrorRequest;
use Sammyjo20\Saloon\Tests\Resources\Connectors\TestConnector;
use Sammyjo20\Saloon\Exceptions\SaloonNoMockResponseFoundException;
use Sammyjo20\Saloon\Tests\Resources\Connectors\QueryParameterConnector;
use Sammyjo20\Saloon\Tests\Resources\Requests\DifferentServiceUserRequest;
use Sammyjo20\Saloon\Tests\Resources\Requests\QueryParameterConnectorRequest;

test('you can create sequence mocks', function () {
    $responseA = new MockResponse(['name' => 'Sammyjo20'], 200);
    $responseB = new MockResponse(['name' => 'Alex'], 200);

    $mockClient = new MockClient([$responseA, $responseB]);

    expect($mockClient->getNextFromSequence())->toEqual($responseA);
    expect($mockClient->getNextFromSequence())->toEqual($responseB);
    expect($mockClient->isEmpty())->toBeTrue();
});

test('you can create connector mocks', function () {
    $responseA = new MockResponse(['name' => 'Sammyjo20'], 200);
    $responseB = new MockResponse(['name' => 'Alex'], 200);

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
    $responseA = new MockResponse(['name' => 'Sammyjo20'], 200);
    $responseB = new MockResponse(['name' => 'Alex'], 200);

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
    $responseA = new MockResponse(['name' => 'Sammyjo20'], 200);
    $responseB = new MockResponse(['name' => 'Alex'], 200);
    $responseC = new MockResponse(['name' => 'Sam Carré'], 200);

    $requestA = new UserRequest;
    $requestB = new ErrorRequest;
    $requestC = new DifferentServiceUserRequest;

    $mockClient = new MockClient([
        'saloon-test.samcarre.dev/api/user' => $responseA, // Test Exact Route
        'saloon-test.samcarre.dev/*' => $responseB, // Test Wildcard Routes
        'google.com/*' => $responseC, // Test Different Route,
    ]);

    expect($mockClient->guessNextResponse($requestA))->toEqual($responseA);
    expect($mockClient->guessNextResponse($requestB))->toEqual($responseB);
    expect($mockClient->guessNextResponse($requestC))->toEqual($responseC);
});

test('you can create wildcard url mocks', function () {
    $responseA = new MockResponse(['name' => 'Sammyjo20'], 200);
    $responseB = new MockResponse(['name' => 'Alex'], 200);
    $responseC = new MockResponse(['name' => 'Sam Carré'], 200);

    $requestA = new UserRequest;
    $requestB = new ErrorRequest;
    $requestC = new DifferentServiceUserRequest;

    $mockClient = new MockClient([
        'saloon-test.samcarre.dev/api/user' => $responseA, // Test Exact Route
        'saloon-test.samcarre.dev/*' => $responseB, // Test Wildcard Routes
        '*' => $responseC,
    ]);

    expect($mockClient->guessNextResponse($requestA))->toEqual($responseA);
    expect($mockClient->guessNextResponse($requestB))->toEqual($responseB);
    expect($mockClient->guessNextResponse($requestC))->toEqual($responseC);
});

test('saloon throws an exception if it cant work out the url response', function () {
    $responseA = new MockResponse(['name' => 'Sammyjo20'], 200);
    $responseB = new MockResponse(['name' => 'Alex'], 200);
    $responseC = new MockResponse(['name' => 'Sam Carré'], 200);

    $requestA = new UserRequest;
    $requestB = new ErrorRequest;
    $requestC = new DifferentServiceUserRequest;

    $mockClient = new MockClient([
        'saloon-test.samcarre.dev/api/user' => $responseA, // Test Exact Route
        'saloon-test.samcarre.dev/*' => $responseB, // Test Wildcard Routes
    ]);

    expect($mockClient->guessNextResponse($requestA))->toEqual($responseA);
    expect($mockClient->guessNextResponse($requestB))->toEqual($responseB);

    $this->expectException(SaloonNoMockResponseFoundException::class);

    expect($mockClient->guessNextResponse($requestC))->toEqual($responseC);
});
