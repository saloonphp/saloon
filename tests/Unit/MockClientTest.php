<?php

use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\Saloon\Tests\Resources\Connectors\QueryParameterConnector;
use Sammyjo20\Saloon\Tests\Resources\Connectors\TestConnector;
use Sammyjo20\Saloon\Tests\Resources\Requests\DifferentServiceUserRequest;
use Sammyjo20\Saloon\Tests\Resources\Requests\QueryParameterConnectorRequest;
use Sammyjo20\Saloon\Tests\Resources\Requests\UserRequest;

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
        QueryParameterConnector::class => $responseB
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
        QueryParameterConnectorRequest::class => $responseB
    ]);

    expect($mockClient->guessNextResponse($requestA))->toEqual($responseA);
    expect($mockClient->guessNextResponse($requestB))->toEqual($responseB);
    expect($mockClient->isEmpty())->toBeFalse();
});

test('you can create wildcard url mocks', function () {
    $responseA = new MockResponse(['name' => 'Sammyjo20'], 200);
    $responseB = new MockResponse(['name' => 'Alex'], 200);
    $responseC = new MockResponse(['name' => 'Sam Carré'], 200);

    $requestA = new UserRequest;
    $requestB = new DifferentServiceUserRequest;

    $mockClient = new MockClient([
        'saloon-test.samcarre.dev/*' => $responseA, // Test Wildcard Routes
        'google.com/*' => $responseB, // Test Different Route
        'saloon-test.samcarre.dev/api/user' => $responseC // Test Exact Route
    ]);

    expect($mockClient->guessNextResponse($requestA))->toEqual($responseA);
    expect($mockClient->guessNextResponse($requestB))->toEqual($responseB);
    expect($mockClient->guessNextResponse($requestA))->toEqual($responseC);
});

test('you can create a callable mock', function () {

});

test('a callable mock must return a saloon response', function () {

});
