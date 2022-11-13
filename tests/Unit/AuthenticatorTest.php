<?php declare(strict_types=1);

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\PendingRequest;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Exceptions\MissingAuthenticatorException;
use Saloon\Tests\Fixtures\Requests\RequiresAuthRequest;
use Saloon\Tests\Fixtures\Authenticators\PizzaAuthenticator;
use Saloon\Tests\Fixtures\Requests\BootAuthenticatorRequest;
use Saloon\Tests\Fixtures\Requests\RequiresBasicAuthRequest;
use Saloon\Tests\Fixtures\Requests\RequiresTokenAuthRequest;
use Saloon\Tests\Fixtures\Requests\RequiresDigestAuthRequest;
use Saloon\Tests\Fixtures\Requests\AuthenticatorPluginRequest;
use Saloon\Tests\Fixtures\Requests\DefaultAuthenticatorRequest;
use Saloon\Tests\Fixtures\Connectors\DefaultAuthenticatorConnector;
use Saloon\Tests\Fixtures\Requests\DefaultPizzaAuthenticatorRequest;

test('you can add an authenticator to a request and it will be applied', function () {
    $request = new DefaultAuthenticatorRequest();
    $pendingRequest = $request->createPendingRequest();

    expect($pendingRequest->headers()->get('Authorization'))->toEqual('Bearer yee-haw-request');
});

test('you can provide a default authenticator on the connector', function () {
    $request = new UserRequest();
    $request->setConnector(new DefaultAuthenticatorConnector);

    $pendingRequest = $request->createPendingRequest();

    expect($pendingRequest->headers()->get('Authorization'))->toEqual('Bearer yee-haw-connector');
});

test('you can provide a default authenticator on the request and it takes priority over the connector', function () {
    $request = new DefaultAuthenticatorRequest();
    $request->setConnector(new DefaultAuthenticatorConnector);

    $pendingRequest = $request->createPendingRequest();

    expect($pendingRequest->headers()->get('Authorization'))->toEqual('Bearer yee-haw-request');
});

test('you can provide an authenticator on the fly and it will take priority over all defaults', function () {
    $request = new DefaultAuthenticatorRequest();
    $request->setConnector(new DefaultAuthenticatorConnector);

    $request->withTokenAuth('yee-haw-on-the-fly', 'PewPew');

    $pendingRequest = $request->createPendingRequest();

    expect($pendingRequest->headers()->get('Authorization'))->toEqual('PewPew yee-haw-on-the-fly');
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
    $request = new UserRequest();
    $request->withAuth(new PizzaAuthenticator('Margherita', 'San Pellegrino'));

    $pendingRequest = $request->createPendingRequest();

    $headers = $pendingRequest->headers()->all();

    expect($headers['X-Pizza'])->toEqual('Margherita');
    expect($headers['X-Drink'])->toEqual('San Pellegrino');
    expect($pendingRequest->config()->get('debug'))->toBeTrue();
});

test('you can use your own authenticators with the authenticate method', function () {
    $request = new UserRequest();
    $request->authenticate(new PizzaAuthenticator('Margherita', 'San Pellegrino'));

    $pendingRequest = $request->createPendingRequest();

    $headers = $pendingRequest->headers()->all();

    expect($headers['X-Pizza'])->toEqual('Margherita');
    expect($headers['X-Drink'])->toEqual('San Pellegrino');
    expect($pendingRequest->config()->get('debug'))->toBeTrue();
});

test('you can use your own authenticators as default', function () {
    $request = new DefaultPizzaAuthenticatorRequest();

    $pendingRequest = $request->createPendingRequest();

    $headers = $pendingRequest->headers()->all();

    expect($headers['X-Pizza'])->toEqual('BBQ Chicken');
    expect($headers['X-Drink'])->toEqual('Lemonade');
    expect($pendingRequest->config()->get('debug'))->toBeTrue();
});

test('you can customise the authenticator inside of the boot method', function () {
    $request = new BootAuthenticatorRequest();

    expect($request->getAuthenticator())->toBeNull();

    $pendingRequest = $request->createPendingRequest();

    expect($pendingRequest->getAuthenticator())->toEqual(new TokenAuthenticator('howdy-partner'));
    expect($pendingRequest->headers()->get('Authorization'))->toEqual('Bearer howdy-partner');
});

test('you can customise the authenticator inside of plugins', function () {
    $request = new AuthenticatorPluginRequest();

    expect($request->getAuthenticator())->toBeNull();

    $pendingRequest = $request->createPendingRequest();

    expect($pendingRequest->getAuthenticator())->toEqual(new TokenAuthenticator('plugin-auth'));
    expect($pendingRequest->headers()->get('Authorization'))->toEqual('Bearer plugin-auth');
});

test('you can customise the authenticator inside of a middleware pipeline', function () {
    $request = new UserRequest;

    expect($request->getAuthenticator())->toBeNull();

    $request->middleware()
        ->onRequest(function (PendingRequest $pendingRequest) {
            $pendingRequest->withTokenAuth('ooh-this-is-cool');
        });

    $pendingRequest = $request->createPendingRequest();

    expect($pendingRequest->getAuthenticator())->toEqual(new TokenAuthenticator('ooh-this-is-cool'));
    expect($pendingRequest->headers()->get('Authorization'))->toEqual('Bearer ooh-this-is-cool');
});
