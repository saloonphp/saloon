<?php

declare(strict_types=1);

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Responses\UserResponse;
use Saloon\Exceptions\NoMockResponseFoundException;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Responses\CustomResponse;
use Saloon\Exceptions\InvalidResponseClassException;
use Saloon\Tests\Fixtures\Requests\InvalidResponseClass;
use Saloon\Tests\Fixtures\Requests\MissingMethodRequest;
use Saloon\Tests\Fixtures\Requests\CustomEndpointRequest;
use Saloon\Tests\Fixtures\Requests\DefaultEndpointRequest;
use Saloon\Tests\Fixtures\Connectors\CustomBaseUrlConnector;
use Saloon\Tests\Fixtures\Connectors\CustomResponseConnector;
use Saloon\Tests\Fixtures\Requests\UserRequestWithCustomResponse;
use Saloon\Tests\Fixtures\Requests\CustomResponseConnectorRequest;

test('if you dont pass in a mock client to the saloon request it will not be in mocking mode', function () {
    $request = new UserRequest();
    $pendingRequest = connector()->createPendingRequest($request);

    expect($pendingRequest->hasMockClient())->toBeFalse();
});

test('you can pass a mock client to the saloon request and it will be in mock mode', function () {
    $request = new UserRequest();
    $mockClient = new MockClient([MockResponse::make([])]);

    $request->withMockClient($mockClient);

    $pendingRequest = connector()->createPendingRequest($request);

    expect($pendingRequest->hasMockClient())->toBeTrue();
    expect($pendingRequest->getMockClient())->toBe($mockClient);
});

test('you cant send a request with a mock client without any responses', function () {
    $mockClient = new MockClient();
    $request = new UserRequest();

    $this->expectException(NoMockResponseFoundException::class);

    connector()->send($request, $mockClient);
});

test('saloon works with a custom response class in connector', function () {
    $request = new CustomResponseConnector();

    expect($request->resolveResponseClass())->toBe(CustomResponse::class);
});

test('saloon can handle with custom response in connector', function () {
    $request = new CustomResponseConnectorRequest();
    $pendingRequest = (new CustomResponseConnector)->createPendingRequest($request);

    expect($pendingRequest->getResponseClass())->toBe(CustomResponse::class);
});

test('saloon can handle with custom response in request', function () {
    $request = new UserRequestWithCustomResponse();

    expect($request->resolveResponseClass())->toBe(UserResponse::class);
});

test('saloon throws an exception if the custom response is not a response class', function () {
    $invalidConnectorClassRequest = new InvalidResponseClass();

    $this->expectException(InvalidResponseClassException::class);

    $connector = new TestConnector;

    $connector->withMockClient(new MockClient([
        InvalidResponseClass::class => MockResponse::make([], 200),
    ]));

    $connector->send($invalidConnectorClassRequest);
});

test('defineEndpoint method may be blank in request class to use the base url', function () {
    $pendingRequest = connector()->createPendingRequest(new DefaultEndpointRequest);

    expect($pendingRequest->getUrl())->toBe(apiUrl());
});

test('a request class can be instantiated using the make method', function () {
    $requestA = UserRequest::make();

    expect($requestA)->toBeInstanceOf(UserRequest::class);
    expect($requestA)->userId->toBeNull();
    expect($requestA)->groupId->toBeNull();

    $requestB = UserRequest::make(1, 2);

    expect($requestB)->toBeInstanceOf(UserRequest::class);
    expect($requestB)->userId->toEqual(1);
    expect($requestB)->groupId->toEqual(2);
});

test('you can join various URLs together', function ($baseUrl, $endpoint, $expected) {
    $connector = new CustomBaseUrlConnector;
    $request = new CustomEndpointRequest;

    $connector->setBaseUrl($baseUrl);
    $request->setEndpoint($endpoint);

    expect($connector->createPendingRequest($request)->getUrl())->toEqual($expected);
})->with([
    ['https://google.com', '/search', 'https://google.com/search'],
    ['https://google.com', 'search', 'https://google.com/search'],
    ['https://google.com/', '/search', 'https://google.com/search'],
    ['https://google.com/', 'search', 'https://google.com/search'],
    ['https://google.com//', '//search', 'https://google.com/search'],
    ['', 'https://google.com/search', 'https://google.com/search'],
    ['', 'google.com/search', '/google.com/search'],
    ['https://google.com', 'https://api.google.com/search', 'https://api.google.com/search'],
]);

test('it throws an exception if you forget to add a method', function () {
    $connector = new TestConnector;
    $request = new MissingMethodRequest;

    $connector->send($request);
})->throws(LogicException::class, 'Your request is missing a HTTP method. You must add a method property like [protected Method $method = Method::GET]');
