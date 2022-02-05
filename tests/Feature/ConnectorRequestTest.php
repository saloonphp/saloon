<?php

use Sammyjo20\Saloon\Exceptions\ClassNotFoundException;
use Sammyjo20\Saloon\Tests\Resources\Requests\UserRequest;
use Sammyjo20\Saloon\Tests\Resources\Requests\ErrorRequest;
use Sammyjo20\Saloon\Exceptions\SaloonInvalidRequestException;
use Sammyjo20\Saloon\Exceptions\SaloonConnectorMethodNotFoundException;
use Sammyjo20\Saloon\Tests\Resources\Connectors\RequestSelectionConnector;
use Sammyjo20\Saloon\Tests\Resources\Connectors\InvalidRequestSelectionConnector;
use Sammyjo20\Saloon\Tests\Resources\Connectors\InvalidDefinedRequestSelectionConnector;

test('you can create a method that will be proxied to a request', function () {
    $connector = new RequestSelectionConnector;
    $request = $connector->getUser();

    expect($request)->toBeInstanceOf(UserRequest::class);
});

test('a request can be called statically', function () {
    $userRequest = RequestSelectionConnector::getMyUser();
    $errorRequest = RequestSelectionConnector::errorRequest();

    expect($userRequest)->toBeInstanceOf(UserRequest::class);
    expect($errorRequest)->toBeInstanceOf(ErrorRequest::class);
});

test('you can pass parameters into the request method', function () {
    $connector = new RequestSelectionConnector;
    $request = $connector->getUser(123, 4);

    expect($request)->toBeInstanceOf(UserRequest::class);
    expect($request)->userId->toEqual(123);
    expect($request)->groupId->toEqual(4);
});

test('you can pass parameters into a guessed request method', function () {
    $connector = new RequestSelectionConnector;
    $request = $connector->getMyUser(123, 4);

    expect($request)->toBeInstanceOf(UserRequest::class);
    expect($request)->userId->toEqual(123);
    expect($request)->groupId->toEqual(4);
});

test('you can pass parameters into the static request method', function () {
    $request = RequestSelectionConnector::getMyUser(123, 4);

    expect($request)->toBeInstanceOf(UserRequest::class);
    expect($request)->userId->toEqual(123);
    expect($request)->groupId->toEqual(4);
});

test('it can call a request from the requests array', function () {
    $connector = new RequestSelectionConnector;

    $userRequest = $connector->getMyUser(); // Manually defined name
    $errorRequest = $connector->errorRequest(); // Guessed name

    expect($userRequest)->toBeInstanceOf(UserRequest::class);
    expect($errorRequest)->toBeInstanceOf(ErrorRequest::class);
});

test('it throws an exception if the request method does not exist on the connector', function () {
    $connector = new RequestSelectionConnector;

    $this->expectException(SaloonConnectorMethodNotFoundException::class);

    $connector->missingRequest();
});

test('it throws an exception if the static request method does not exist on the connector', function () {
    $this->expectException(SaloonConnectorMethodNotFoundException::class);

    RequestSelectionConnector::missingRequest();
});

test('it throws an exception if one of the provided request classes does not exist', function () {
    $connector = new InvalidRequestSelectionConnector;

    $this->expectException(ReflectionException::class);

    $connector->missingClass();
});

test('it throws an exception if one of the provided guessed request classes does not exist', function () {
    $connector = new InvalidDefinedRequestSelectionConnector;

    $this->expectException(ClassNotFoundException::class);

    $connector->missing_request();
});

test('it throws an exception if one of the provided request classes is not a saloon request', function () {
    $connector = new InvalidDefinedRequestSelectionConnector;

    $this->expectException(SaloonInvalidRequestException::class);

    $connector->test_connector();
});
