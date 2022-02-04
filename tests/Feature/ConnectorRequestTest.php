<?php

use Sammyjo20\Saloon\Exceptions\SaloonMethodNotFoundException;
use Sammyjo20\Saloon\Tests\Resources\Connectors\RequestSelectionConnector;
use Sammyjo20\Saloon\Tests\Resources\Requests\ErrorRequest;
use Sammyjo20\Saloon\Tests\Resources\Requests\UserRequest;

test('you can create a method that will be proxied to a request', function () {
    $connector = new RequestSelectionConnector;
    $request = $connector->getUser();

    expect($request)->toBeInstanceOf(UserRequest::class);
});

test('you can pass parameters into the request method', function () {
    $connector = new RequestSelectionConnector;
    $request = $connector->getUser(123, 4);

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

    $this->expectException(SaloonMethodNotFoundException::class);

    $connector->missingRequest();
});

test('it throws an exception if one of the provided request classes does not exist', function () {
    //
});

test('it throws an exception if one of the provided request classes is not a saloon request', function () {
    //
});
