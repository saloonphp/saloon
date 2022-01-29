<?php

use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Tests\Resources\Requests\UserRequest;
use Sammyjo20\Saloon\Tests\Resources\Responses\UserResponse;
use Sammyjo20\Saloon\Tests\Resources\Responses\CustomResponse;
use Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException;
use Sammyjo20\Saloon\Tests\Resources\Requests\NoConnectorRequest;
use Sammyjo20\Saloon\Tests\Resources\Connectors\ExtendedConnector;
use Sammyjo20\Saloon\Tests\Resources\Requests\InvalidResponseClass;
use Sammyjo20\Saloon\Exceptions\SaloonInvalidResponseClassException;
use Sammyjo20\Saloon\Tests\Resources\Requests\DefaultEndpointRequest;
use Sammyjo20\Saloon\Tests\Resources\Requests\InvalidConnectorRequest;
use Sammyjo20\Saloon\Exceptions\SaloonNoMockResponsesProvidedException;
use Sammyjo20\Saloon\Tests\Resources\Requests\ExtendedConnectorRequest;
use Sammyjo20\Saloon\Tests\Resources\Connectors\CustomResponseConnector;
use Sammyjo20\Saloon\Tests\Resources\Connectors\InvalidResponseConnector;
use Sammyjo20\Saloon\Tests\Resources\Requests\InvalidConnectorClassRequest;
use Sammyjo20\Saloon\Tests\Resources\Requests\UserRequestWithCustomResponse;
use Sammyjo20\Saloon\Tests\Resources\Requests\CustomResponseConnectorRequest;

test('if you dont pass in a mock client to the saloon request it will not be in mocking mode', function () {
    $request = new UserRequest();
    $requestManager = $request->getRequestManager();

    expect($requestManager->isMocking())->toBeFalse();
});

test('you can pass a mock client to the saloon request and it will be in mock mode', function () {
    $request = new UserRequest();
    $mockClient = new MockClient([new MockResponse([], 200)]);

    $requestManager = $request->getRequestManager($mockClient);

    expect($requestManager->isMocking())->toBeTrue();
});

test('you cant pass a mock client without any responses', function () {
    $mockClient = new MockClient();
    $request = new UserRequest();

    $this->expectException(SaloonNoMockResponsesProvidedException::class);

    $request->send($mockClient);
});

test('saloon throws an exception if if no connector is specified', function () {
    $noConnectorRequest = new NoConnectorRequest;

    $this->expectException(SaloonInvalidConnectorException::class);

    expect($noConnectorRequest->getConnector());
});

test('saloon throws an exception if the connector is invalid', function () {
    $invalidConnectorRequest = new InvalidConnectorRequest;

    $this->expectException(SaloonInvalidConnectorException::class);

    expect($invalidConnectorRequest->getConnector());
});

test('saloon throws an exception if the connector is not a connector class', function () {
    $invalidConnectorClassRequest = new InvalidConnectorClassRequest;

    $this->expectException(SaloonInvalidConnectorException::class);

    expect($invalidConnectorClassRequest->getConnector());
});

test('saloon works even if you have an extended connector', function () {
    $request = new ExtendedConnectorRequest;

    expect($request->getConnector())->toBeInstanceOf(ExtendedConnector::class);
});

test('saloon works with a custom response class in connector', function () {
    $request = new CustomResponseConnector();

    expect($request->getResponseClass())->toBe(CustomResponse::class);
});

test('saloon throws an exception if the extended connector return a response is not a response class', function () {
    $invalidConnectorResponse = new InvalidResponseConnector();

    $this->expectException(SaloonInvalidResponseClassException::class);

    $invalidConnectorResponse->getResponseClass();
});

test('saloon can handle with custom response in connector', function () {
    $request = new CustomResponseConnectorRequest();

    expect($request->getResponseClass())->toBe(CustomResponse::class);
});

test('saloon can handle with custom response in request', function () {
    $request = new UserRequestWithCustomResponse();

    expect($request->getResponseClass())->toBe(UserResponse::class);
});

test('saloon can handle with custom response in request and custom response in connector', function () {
    $request = new CustomResponseConnectorRequest();

    expect($request->getResponseClass())->toBe(CustomResponse::class);
});

test('saloon throws an exception if the custom response is not a response class', function () {
    $invalidConnectorClassRequest = new InvalidResponseClass();

    $this->expectException(SaloonInvalidResponseClassException::class);

    expect($invalidConnectorClassRequest->getResponseClass());
});

test('defineEndpoint method may be omited in request class to use the base url')
    ->expect(new DefaultEndpointRequest)
    ->getFullRequestUrl()->toBe(apiUrl());
