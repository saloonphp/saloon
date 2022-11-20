<?php

declare(strict_types=1);

use Saloon\Tests\Fixtures\Requests\UserWithTestHandlerRequest;
use Saloon\Tests\Fixtures\Requests\UserWithTestHandlerConnectorRequest;

test('you can define a handler within a plugin', function () {
    $request = new UserWithTestHandlerRequest();
    $response = $request->send();

    $headers = $response->headers()->all();

    expect(isset($headers['X-Test-Handler']))->toBeTrue();
    expect($headers['X-Test-Handler'])->toEqual([1]);
});

test('handlers on the request with the same name will take priority', function () {
    $request = new UserWithTestHandlerConnectorRequest();
    $response = $request->send();

    $headers = $response->headers()->all();

    expect(isset($headers['X-Handler-Added-To']))->toBeTrue();
    expect($headers['X-Handler-Added-To'])->toEqual(['connector']);
});
