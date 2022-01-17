<?php

use Sammyjo20\Saloon\Exceptions\SaloonRequestException;
use Sammyjo20\Saloon\Tests\Resources\Requests\InterceptedResponseRequest;
use Sammyjo20\Saloon\Tests\Resources\Requests\InterceptedConnectorErrorRequest;

test('a connector response can be intercepted', function () {
    $request = new InterceptedConnectorErrorRequest();

    $this->expectException(SaloonRequestException::class);

    $request->send();
});

test('a request response can be intercepted', function () {
    $request = new InterceptedResponseRequest();

    $response = $request->send();

    expect($response->isCached())->toBeTrue();
});
