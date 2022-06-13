<?php

use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\UserRequest;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\ErrorRequest;
use Sammyjo20\Saloon\Tests\Fixtures\Connectors\TestConnector;
use Sammyjo20\Saloon\Exceptions\SaloonNoMockResponsesProvidedException;
use Sammyjo20\Saloon\Tests\Fixtures\Connectors\QueryParameterConnector;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\DifferentServiceUserRequest;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\QueryParameterConnectorRequest;

test('a request can be mocked with a sequence', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam'], 200),
        MockResponse::make(['name' => 'Alex'], 200),
        MockResponse::make(['error' => 'Server Unavailable'], 500),
    ]);

    $responseA = (new UserRequest)->send($mockClient);

    expect($responseA->isMocked())->toBeTrue();
    expect($responseA->json())->toEqual(['name' => 'Sam']);
    expect($responseA->status())->toEqual(200);

    $responseB = (new UserRequest)->send($mockClient);

    expect($responseB->isMocked())->toBeTrue();
    expect($responseB->json())->toEqual(['name' => 'Alex']);
    expect($responseB->status())->toEqual(200);

    $responseC = (new UserRequest)->send($mockClient);

    expect($responseC->isMocked())->toBeTrue();
    expect($responseC->json())->toEqual(['error' => 'Server Unavailable']);
    expect($responseC->status())->toEqual(500);

    $this->expectException(SaloonNoMockResponsesProvidedException::class);

    (new UserRequest)->send($mockClient);
});

test('a request can be mocked with a connector defined', function () {
    $responseA = MockResponse::make(['name' => 'Sammyjo20'], 200);
    $responseB = MockResponse::make(['name' => 'Alex'], 200);

    $connectorARequest = new UserRequest;
    $connectorBRequest = new QueryParameterConnectorRequest;

    $mockClient = new MockClient([
        TestConnector::class => $responseA,
        QueryParameterConnector::class => $responseB,
    ]);

    $responseA = $connectorARequest->send($mockClient);

    expect($responseA->isMocked())->toBeTrue();
    expect($responseA->json())->toEqual(['name' => 'Sammyjo20']);
    expect($responseA->status())->toEqual(200);

    $responseB = $connectorBRequest->send($mockClient);

    expect($responseB->isMocked())->toBeTrue();
    expect($responseB->json())->toEqual(['name' => 'Alex']);
    expect($responseB->status())->toEqual(200);
});

test('a request can be mocked with a request defined', function () {
    $responseA = MockResponse::make(['name' => 'Sammyjo20'], 200);
    $responseB = MockResponse::make(['name' => 'Alex'], 200);

    $requestA = new UserRequest;
    $requestB = new QueryParameterConnectorRequest;

    $mockClient = new MockClient([
        UserRequest::class => $responseA,
        QueryParameterConnectorRequest::class => $responseB,
    ]);

    $responseA = $requestA->send($mockClient);

    expect($responseA->isMocked())->toBeTrue();
    expect($responseA->json())->toEqual(['name' => 'Sammyjo20']);
    expect($responseA->status())->toEqual(200);

    $responseB = $requestB->send($mockClient);

    expect($responseB->isMocked())->toBeTrue();
    expect($responseB->json())->toEqual(['name' => 'Alex']);
    expect($responseB->status())->toEqual(200);
});

test('a request can be mocked with a url defined', function () {
    $responseA = MockResponse::make(['name' => 'Sammyjo20'], 200);
    $responseB = MockResponse::make(['name' => 'Alex'], 200);
    $responseC = MockResponse::make(['error' => 'Server Broken'], 500);

    $requestA = new UserRequest;
    $requestB = new ErrorRequest;
    $requestC = new DifferentServiceUserRequest;

    $mockClient = new MockClient([
        'tests.saloon.dev/api/user' => $responseA, // Test Exact Route
        'tests.saloon.dev/*' => $responseB, // Test Wildcard Routes
        'google.com/*' => $responseC, // Test Different Route,
    ]);

    $responseA = $requestA->send($mockClient);

    expect($responseA->isMocked())->toBeTrue();
    expect($responseA->json())->toEqual(['name' => 'Sammyjo20']);
    expect($responseA->status())->toEqual(200);

    $responseB = $requestB->send($mockClient);

    expect($responseB->isMocked())->toBeTrue();
    expect($responseB->json())->toEqual(['name' => 'Alex']);
    expect($responseB->status())->toEqual(200);

    $responseC = $requestC->send($mockClient);

    expect($responseC->isMocked())->toBeTrue();
    expect($responseC->json())->toEqual(['error' => 'Server Broken']);
    expect($responseC->status())->toEqual(500);
});

test('you can create wildcard url mocks', function () {
    $responseA = MockResponse::make(['name' => 'Sammyjo20'], 200);
    $responseB = MockResponse::make(['name' => 'Alex'], 200);
    $responseC = MockResponse::make(['error' => 'Server Broken'], 500);

    $requestA = new UserRequest;
    $requestB = new ErrorRequest;
    $requestC = new DifferentServiceUserRequest;

    $mockClient = new MockClient([
        'tests.saloon.dev/api/user' => $responseA, // Test Exact Route
        'tests.saloon.dev/*' => $responseB, // Test Wildcard Routes
        '*' => $responseC,
    ]);

    $responseA = $requestA->send($mockClient);

    expect($responseA->isMocked())->toBeTrue();
    expect($responseA->json())->toEqual(['name' => 'Sammyjo20']);
    expect($responseA->status())->toEqual(200);

    $responseB = $requestB->send($mockClient);

    expect($responseB->isMocked())->toBeTrue();
    expect($responseB->json())->toEqual(['name' => 'Alex']);
    expect($responseB->status())->toEqual(200);

    $responseC = $requestC->send($mockClient);

    expect($responseC->isMocked())->toBeTrue();
    expect($responseC->json())->toEqual(['error' => 'Server Broken']);
    expect($responseC->status())->toEqual(500);
});

test('you can use a closure for the mock response', function () {
    $sequenceMock = new MockClient([
        function (SaloonRequest $request): MockResponse {
            return new MockResponse(['request' => $request->getFullRequestUrl()]);
        },
    ]);

    $sequenceResponse = UserRequest::make()->send($sequenceMock);

    expect($sequenceResponse->isMocked())->toBeTrue();
    expect($sequenceResponse->json())->toEqual(['request' => 'https://tests.saloon.dev/api/user']);

    // Connector mock

    $connectorMock = new MockClient([
        TestConnector::class => function (SaloonRequest $request): MockResponse {
            return new MockResponse(['request' => $request->getFullRequestUrl()]);
        },
    ]);

    $connectorResponse = UserRequest::make()->send($connectorMock);

    expect($connectorResponse->isMocked())->toBeTrue();
    expect($connectorResponse->json())->toEqual(['request' => 'https://tests.saloon.dev/api/user']);

    // Request mock

    $requestMock = new MockClient([
        UserRequest::class => function (SaloonRequest $request): MockResponse {
            return new MockResponse(['request' => $request->getFullRequestUrl()]);
        },
    ]);

    $requestResponse = UserRequest::make()->send($requestMock);

    expect($requestResponse->isMocked())->toBeTrue();
    expect($requestResponse->json())->toEqual(['request' => 'https://tests.saloon.dev/api/user']);

    // URL mock

    $urlMock = new MockClient([
        'tests.saloon.dev/*' => function (SaloonRequest $request): MockResponse {
            return new MockResponse(['request' => $request->getFullRequestUrl()]);
        },
    ]);

    $urlResponse = UserRequest::make()->send($urlMock);

    expect($urlResponse->isMocked())->toBeTrue();
    expect($urlResponse->json())->toEqual(['request' => 'https://tests.saloon.dev/api/user']);
});
