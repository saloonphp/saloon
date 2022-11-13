<?php declare(strict_types=1);

use Saloon\Exceptions\SaloonRequestException;
use Saloon\Tests\Fixtures\Requests\InterceptedResponseRequest;
use Saloon\Tests\Fixtures\Requests\InterceptedConnectorErrorRequest;

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
