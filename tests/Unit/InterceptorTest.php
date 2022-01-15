<?php

use Sammyjo20\Saloon\Exceptions\SaloonRequestException;
use Sammyjo20\Saloon\Tests\Resources\Requests\InterceptedConnectorErrorRequest;
use Sammyjo20\Saloon\Tests\Resources\Requests\InterceptedConnectorUserRequest;
use Sammyjo20\Saloon\Tests\Resources\Requests\InterceptedRequest;
use Sammyjo20\Saloon\Tests\Resources\Requests\InterceptedResponseRequest;

test('a connector request can be intercepted', function () {
    $request = new InterceptedConnectorUserRequest();

    $response = $request->send();

    expect($response->getSaloonRequestOptions()['headers'])->toHaveKey('X-Connector-Name', 'Interceptor');
});

test('a connector response can be intercepted', function () {
    $request = new InterceptedConnectorErrorRequest();

    $this->expectException(SaloonRequestException::class);

    $request->send();
});

test('a request can be intercepted', function () {
    $request = new InterceptedRequest();

    $response = $request->send();

    expect($response->getSaloonRequestOptions()['headers'])->toHaveKey('X-Intercepted-Header', 'Sam');
});

test('a response can be intercepted', function () {
    $request = new InterceptedResponseRequest();

    $this->expectException(SaloonRequestException::class);

    $request->send();
});
