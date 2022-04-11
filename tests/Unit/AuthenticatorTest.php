<?php

use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Exceptions\MissingAuthenticatorException;
use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\RequiresAuthRequest;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\RequiresBasicAuthRequest;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\RequiresDigestAuthRequest;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\RequiresTokenAuthRequest;

test('you can add an authenticator to a request and it will be applied', function () {

});

test('you can provide a default authenticator on the connector', function () {

});

test('you can provide a default authenticator on the request and it takes priority over the connector', function () {

});

test('you can provide an authenticator on the fly and it will take priority over all defaults', function () {

});

test('the RequiresAuth trait will throw an exception if an authenticator is not found', function () {
    $mockClient = new MockClient([
        MockResponse::make(),
    ]);

    $this->expectException(MissingAuthenticatorException::class);
    $this->expectExceptionMessage('This request requires authentication. Please provide an authenticator using the `withAuth` method.');

    $request = new RequiresAuthRequest();
    $request->send($mockClient);
});

test('the RequiresTokenAuth trait will throw an exception if an authenticator is not found', function () {
    $mockClient = new MockClient([
        MockResponse::make(),
    ]);

    $this->expectException(MissingAuthenticatorException::class);
    $this->expectExceptionMessage('This request requires token authentication. Please provide authentication using the `withTokenAuth` method.');

    $request = new RequiresTokenAuthRequest();
    $request->send($mockClient);
});

test('the RequiresBasicAuth trait will throw an exception if an authenticator is not found', function () {
    $mockClient = new MockClient([
        MockResponse::make(),
    ]);

    $this->expectException(MissingAuthenticatorException::class);
    $this->expectExceptionMessage('This request requires basic authentication. Please provide authentication using the `withBasicAuth` method.');

    $request = new RequiresBasicAuthRequest();
    $request->send($mockClient);
});

test('the RequiresDigestAuth trait will throw an exception if an authenticator is not found', function () {
    $mockClient = new MockClient([
        MockResponse::make(),
    ]);

    $this->expectException(MissingAuthenticatorException::class);
    $this->expectExceptionMessage('This request requires digest authentication. Please provide authentication using the `withDigestAuth` method.');

    $request = new RequiresDigestAuthRequest();
    $request->send($mockClient);
});
