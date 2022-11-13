<?php declare(strict_types=1);

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Responses\UserResponse;
use Saloon\Tests\Fixtures\Responses\CustomResponse;
use Saloon\Exceptions\InvalidConnectorException;
use Saloon\Tests\Fixtures\Requests\NoConnectorRequest;
use Saloon\Tests\Fixtures\Connectors\ExtendedConnector;
use Saloon\Tests\Fixtures\Connectors\TestProxyConnector;
use Saloon\Tests\Fixtures\Requests\InvalidResponseClass;
use Saloon\Exceptions\NoMockResponseFoundException;
use Saloon\Exceptions\InvalidResponseClassException;
use Saloon\Tests\Fixtures\Requests\DefaultEndpointRequest;
use Saloon\Tests\Fixtures\Requests\InvalidConnectorRequest;
use Saloon\Tests\Fixtures\Requests\ExtendedConnectorRequest;
use Saloon\Tests\Fixtures\Connectors\CustomResponseConnector;
use Saloon\Tests\Fixtures\Connectors\InvalidResponseConnector;
use Saloon\Tests\Fixtures\Requests\InvalidConnectorClassRequest;
use Saloon\Tests\Fixtures\Requests\UserRequestWithCustomResponse;
use Saloon\Tests\Fixtures\Requests\CustomResponseConnectorRequest;

test('if you dont pass in a mock client to the saloon request it will not be in mocking mode', function () {
    $request = new UserRequest();
    $pendingRequest = $request->createPendingRequest();

    expect($pendingRequest->isMocking())->toBeFalse();
});

test('you can pass a mock client to the saloon request and it will be in mock mode', function () {
    $request = new UserRequest();
    $mockClient = new MockClient([MockResponse::make([], 200)]);

    $request->withMockClient($mockClient);

    $pendingRequest = $request->createPendingRequest();

    expect($pendingRequest->isMocking())->toBeTrue();
});

test('you cant send a request with a mock client without any responses', function () {
    $mockClient = new MockClient();
    $request = new UserRequest();

    $this->expectException(NoMockResponseFoundException::class);

    $request->send($mockClient);
});

test('saloon throws an exception if if no connector is specified', function () {
    $noConnectorRequest = new NoConnectorRequest;

    $this->expectException(InvalidConnectorException::class);

    expect($noConnectorRequest->getConnector());
});

test('saloon throws an exception if the connector is invalid', function () {
    $invalidConnectorRequest = new InvalidConnectorRequest;

    $this->expectException(InvalidConnectorException::class);

    expect($invalidConnectorRequest->getConnector());
});

test('saloon throws an exception if the connector is not a connector class', function () {
    $invalidConnectorClassRequest = new InvalidConnectorClassRequest;

    $this->expectException(InvalidConnectorException::class);

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

    $this->expectException(InvalidResponseClassException::class);

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

    $this->expectException(InvalidResponseClassException::class);

    expect($invalidConnectorClassRequest->getResponseClass());
});

test('defineEndpoint method may be blank in request class to use the base url', function () {
    expect(new DefaultEndpointRequest)->getRequestUrl()->toBe(apiUrl());
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

test('a method is proxied onto the connector if it does not exist on the request', function () {
    $connector = new TestProxyConnector;
    $request = $connector->request(new UserRequest);

    expect(method_exists($request, 'greeting'))->toBeFalse();
    expect($request->greeting())->toEqual('Howdy!');
});
