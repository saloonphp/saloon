<?php

use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\UserRequest;
use Sammyjo20\Saloon\Exceptions\MissingAuthenticatorException;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\RequiresAuthRequest;
use Sammyjo20\Saloon\Tests\Fixtures\Authenticators\PizzaAuthenticator;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\RequiresBasicAuthRequest;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\RequiresTokenAuthRequest;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\RequiresDigestAuthRequest;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\DefaultAuthenticatorRequest;
use Sammyjo20\Saloon\Tests\Fixtures\Connectors\DefaultAuthenticatorConnector;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\DefaultPizzaAuthenticatorRequest;

test('you can add an authenticator to a request and it will be applied', function () {
    $request = new DefaultAuthenticatorRequest();
    $requestManager = $request->getRequestManager();

    $requestManager->hydrate();

    expect($requestManager->getHeader('Authorization'))->toEqual('Bearer yee-haw-request');
});

test('you can provide a default authenticator on the connector', function () {
    $request = new UserRequest();
    $request->setConnector(new DefaultAuthenticatorConnector);

    $requestManager = $request->getRequestManager();

    $requestManager->hydrate();

    expect($requestManager->getHeader('Authorization'))->toEqual('Bearer yee-haw-connector');
});

test('you can provide a default authenticator on the request and it takes priority over the connector', function () {
    $request = new DefaultAuthenticatorRequest();
    $request->setConnector(new DefaultAuthenticatorConnector);

    $requestManager = $request->getRequestManager();

    $requestManager->hydrate();

    expect($requestManager->getHeader('Authorization'))->toEqual('Bearer yee-haw-request');
});

test('you can provide an authenticator on the fly and it will take priority over all defaults', function () {
    $request = new DefaultAuthenticatorRequest();
    $request->setConnector(new DefaultAuthenticatorConnector);

    $request->withTokenAuth('yee-haw-on-the-fly', 'PewPew');

    $requestManager = $request->getRequestManager();

    $requestManager->hydrate();

    expect($requestManager->getHeader('Authorization'))->toEqual('PewPew yee-haw-on-the-fly');
});

test('the RequiresAuth trait will throw an exception if an authenticator is not found', function () {
    $mockClient = new MockClient([
        MockResponse::make(),
    ]);

    $this->expectException(MissingAuthenticatorException::class);
    $this->expectExceptionMessage('The "Sammyjo20\Saloon\Tests\Fixtures\Requests\RequiresAuthRequest" request requires authentication. Please provide an authenticator using the `withAuth` method or return a default authenticator in your connector/request.');

    $request = new RequiresAuthRequest();
    $request->send($mockClient);
});

test('the RequiresTokenAuth trait will throw an exception if an authenticator is not found', function () {
    $mockClient = new MockClient([
        MockResponse::make(),
    ]);

    $this->expectException(MissingAuthenticatorException::class);
    $this->expectExceptionMessage('The "Sammyjo20\Saloon\Tests\Fixtures\Requests\RequiresTokenAuthRequest" request requires authentication. Please provide authentication using the `withTokenAuth` method or return a default authenticator in your connector/request.');

    $request = new RequiresTokenAuthRequest();
    $request->send($mockClient);
});

test('the RequiresBasicAuth trait will throw an exception if an authenticator is not found', function () {
    $mockClient = new MockClient([
        MockResponse::make(),
    ]);

    $this->expectException(MissingAuthenticatorException::class);
    $this->expectExceptionMessage('The "Sammyjo20\Saloon\Tests\Fixtures\Requests\RequiresBasicAuthRequest" request requires authentication. Please provide authentication using the `withBasicAuth` method or return a default authenticator in your connector/request.');

    $request = new RequiresBasicAuthRequest();
    $request->send($mockClient);
});

test('the RequiresDigestAuth trait will throw an exception if an authenticator is not found', function () {
    $mockClient = new MockClient([
        MockResponse::make(),
    ]);

    $this->expectException(MissingAuthenticatorException::class);
    $this->expectExceptionMessage('The "Sammyjo20\Saloon\Tests\Fixtures\Requests\RequiresDigestAuthRequest" request requires authentication. Please provide authentication using the `withDigestAuth` method or return a default authenticator in your connector/request.');

    $request = new RequiresDigestAuthRequest();
    $request->send($mockClient);
});

test('you can use your own authenticators', function () {
    $mockClient = new MockClient([
        MockResponse::make(),
    ]);

    $request = new UserRequest();
    $request->withAuth(new PizzaAuthenticator('Margherita', 'San Pellegrino'));
    $request->send($mockClient);

    $headers = $request->getHeaders();

    expect($headers['X-Pizza'])->toEqual('Margherita');
    expect($headers['X-Drink'])->toEqual('San Pellegrino');
    expect($request->getConfig('debug'))->toBeTrue();
});

test('you can use your own authenticators as default', function () {
    $mockClient = new MockClient([
        MockResponse::make(),
    ]);

    $request = new DefaultPizzaAuthenticatorRequest();
    $request->send($mockClient);

    $headers = $request->getHeaders();

    expect($headers['X-Pizza'])->toEqual('BBQ Chicken');
    expect($headers['X-Drink'])->toEqual('Lemonade');
    expect($request->getConfig('debug'))->toBeTrue();
});
