<?php

declare(strict_types=1);

use Saloon\Exceptions\ClassNotFoundException;
use Saloon\Http\Groups\AnonymousRequestGroup;
use Saloon\Exceptions\InvalidRequestException;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Collections\UserGroup;
use Saloon\Tests\Fixtures\Requests\ErrorRequest;
use Saloon\Exceptions\InvalidRequestKeyException;
use Saloon\Tests\Fixtures\Collections\GuessedGroup;
use Saloon\Exceptions\ConnectorMethodNotFoundException;
use Saloon\Tests\Fixtures\Connectors\ServiceRequestConnector;
use Saloon\Tests\Fixtures\Connectors\RequestSelectionConnector;
use Saloon\Tests\Fixtures\Connectors\InvalidServiceRequestConnector;
use Saloon\Tests\Fixtures\Connectors\InvalidRequestSelectionConnector;
use Saloon\Tests\Fixtures\Connectors\InvalidDefinedRequestSelectionConnector;

test('a request can be called statically', function () {
    $userRequest = RequestSelectionConnector::getMyUser();
    $errorRequest = RequestSelectionConnector::errorRequest();

    expect($userRequest)->toBeInstanceOf(UserRequest::class);
    expect($errorRequest)->toBeInstanceOf(ErrorRequest::class);
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

    $userRequest = $connector->getMyUser(123, 4); // Manually defined name
    $errorRequest = $connector->errorRequest(); // Guessed name

    expect($userRequest)->toBeInstanceOf(UserRequest::class);
    expect($errorRequest)->toBeInstanceOf(ErrorRequest::class);
});

test('it throws an exception if the request method does not exist on the connector', function () {
    $connector = new RequestSelectionConnector;

    $this->expectException(ConnectorMethodNotFoundException::class);

    $connector->missingRequest();
});

test('it throws an exception if the static request method does not exist on the connector', function () {
    $this->expectException(ConnectorMethodNotFoundException::class);

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

    $this->expectException(InvalidRequestException::class);

    $connector->test_connector();
});

test('a connector request can be defined in an array', function () {
    $connector = new ServiceRequestConnector;
    $request = $connector->user();

    expect($request)->toBeInstanceOf(AnonymousRequestGroup::class);
    expect($request->get())->toBeInstanceOf(UserRequest::class);
    expect($request->get(1)->userId)->toEqual(1);
});

test('a connector request collection can be defined', function () {
    $connector = new ServiceRequestConnector;
    $request = $connector->custom();

    expect($request)->toBeInstanceOf(UserGroup::class);
    expect($request->get())->toBeInstanceOf(UserRequest::class);
});

test('it throws an exception if you do not key an array of requests', function () {
    $connector = new InvalidServiceRequestConnector();

    $this->expectException(InvalidRequestKeyException::class);
    $this->expectDeprecationMessage('Request groups must be keyed.');

    $connector->custom();
});

test('it can guess the name of a group', function () {
    $connector = new ServiceRequestConnector;
    $group = $connector->guessedGroup();

    expect($group)->toBeInstanceOf(GuessedGroup::class);
});
