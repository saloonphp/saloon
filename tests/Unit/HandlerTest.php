<?php

use Sammyjo20\Saloon\Tests\Resources\Requests\UserWithTestHandlerRequest;
use Sammyjo20\Saloon\Tests\Resources\Requests\UserWithTestHandlerConnectorRequest;

test('you can define a handler within a plugin', function () {
    $request = new UserWithTestHandlerRequest();
    $response = $request->send();

    $headers = $response->headers();

    expect(isset($headers['X-Test-Handler']))->toBeTrue();
    expect($headers['X-Test-Handler'])->toEqual([1]);
});

test('handlers on the request with the same name will take priority', function () {
    $request = new UserWithTestHandlerConnectorRequest();
    $response = $request->send();

    $headers = $response->headers();

    expect(isset($headers['X-Handler-Added-To']))->toBeTrue();
    expect($headers['X-Handler-Added-To'])->toEqual(['request']);
});
