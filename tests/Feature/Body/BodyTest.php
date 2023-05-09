<?php

use Saloon\Exceptions\InvalidBodyReturnTypeException;
use Saloon\Tests\Fixtures\Connectors\InvalidBodyReturnTypeConnector;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Requests\InvalidBodyReturnTypeRequest;
use Saloon\Tests\Fixtures\Requests\UserRequest;

test('if the body method on the connector does not return a BodyRepository it will throw an exception', function () {
    $connector = new InvalidBodyReturnTypeConnector;
    $request = new UserRequest;

    $this->expectException(InvalidBodyReturnTypeException::class);
    $this->expectExceptionMessage('The `body()` method on your connector must return an instance of Saloon\Contracts\Body\BodyRepository.');

    $connector->send($request);
});

test('if the body method on the request does not return a BodyRepository it will throw an exception', function () {
    $connector = new TestConnector;
    $request = new InvalidBodyReturnTypeRequest;

    $this->expectException(InvalidBodyReturnTypeException::class);
    $this->expectExceptionMessage('The `body()` method on your request must return an instance of Saloon\Contracts\Body\BodyRepository.');

    $connector->send($request);
});
