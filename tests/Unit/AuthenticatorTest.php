<?php

declare(strict_types=1);

use Saloon\Http\PendingRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Exceptions\MissingAuthenticatorException;
use Saloon\Tests\Fixtures\Requests\RequiresAuthRequest;
use Saloon\Tests\Fixtures\Authenticators\PizzaAuthenticator;
use Saloon\Tests\Fixtures\Requests\BootAuthenticatorRequest;
use Saloon\Tests\Fixtures\Requests\AuthenticatorPluginRequest;
use Saloon\Tests\Fixtures\Requests\DefaultAuthenticatorRequest;
use Saloon\Tests\Fixtures\Connectors\DefaultAuthenticatorConnector;
use Saloon\Tests\Fixtures\Requests\DefaultPizzaAuthenticatorRequest;

test('you can add an authenticator to a request and it will be applied', function () {
    $request = new DefaultAuthenticatorRequest();
    $pendingRequest = connector()->createPendingRequest($request);

    expect($pendingRequest->headers()->get('Authorization'))->toEqual('Bearer yee-haw-request');
});

test('you can provide a default authenticator on the connector', function () {
    $request = new UserRequest();
    $connector = new DefaultAuthenticatorConnector;

    $pendingRequest = $connector->createPendingRequest($request);

    expect($pendingRequest->headers()->get('Authorization'))->toEqual('Bearer yee-haw-connector');
});

test('you can provide a default authenticator on the request and it takes priority over the connector', function () {
    $request = new DefaultAuthenticatorRequest();
    $connector = new DefaultAuthenticatorConnector;

    $pendingRequest = $connector->createPendingRequest($request);

    expect($pendingRequest->headers()->get('Authorization'))->toEqual('Bearer yee-haw-request');
});

test('you can provide an authenticator on the fly and it will take priority over all defaults', function () {
    $request = new DefaultAuthenticatorRequest();
    $connector = new DefaultAuthenticatorConnector;

    $request->withTokenAuth('yee-haw-on-the-fly', 'PewPew');

    $pendingRequest = $connector->createPendingRequest($request);

    expect($pendingRequest->headers()->get('Authorization'))->toEqual('PewPew yee-haw-on-the-fly');
});

test('the RequiresAuth trait will throw an exception if an authenticator is not found', function () {
    $mockClient = new MockClient([
        MockResponse::make(),
    ]);

    $this->expectException(MissingAuthenticatorException::class);
    $this->expectExceptionMessage('The "Saloon\Tests\Fixtures\Requests\RequiresAuthRequest" request requires authentication.');

    $request = new RequiresAuthRequest();

    connector()->send($request, $mockClient);
});

test('you can use your own authenticators', function () {
    $request = new UserRequest();
    $request->authenticate(new PizzaAuthenticator('Margherita', 'San Pellegrino'));

    $pendingRequest = connector()->createPendingRequest($request);

    $headers = $pendingRequest->headers()->all();

    expect($headers['X-Pizza'])->toEqual('Margherita');
    expect($headers['X-Drink'])->toEqual('San Pellegrino');
    expect($pendingRequest->config()->get('debug'))->toBeTrue();
});

test('you can use your own authenticators as default', function () {
    $request = new DefaultPizzaAuthenticatorRequest();

    $pendingRequest = connector()->createPendingRequest($request);

    $headers = $pendingRequest->headers()->all();

    expect($headers['X-Pizza'])->toEqual('BBQ Chicken');
    expect($headers['X-Drink'])->toEqual('Lemonade');
    expect($pendingRequest->config()->get('debug'))->toBeTrue();
});

test('you can customise the authenticator inside of the boot method', function () {
    $request = new BootAuthenticatorRequest();

    expect($request->getAuthenticator())->toBeNull();

    $pendingRequest = connector()->createPendingRequest($request);

    expect($pendingRequest->getAuthenticator())->toEqual(new TokenAuthenticator('howdy-partner'));
    expect($pendingRequest->headers()->get('Authorization'))->toEqual('Bearer howdy-partner');
});

test('you can customise the authenticator inside of plugins', function () {
    $request = new AuthenticatorPluginRequest();

    expect($request->getAuthenticator())->toBeNull();

    $pendingRequest = connector()->createPendingRequest($request);

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

    $pendingRequest = connector()->createPendingRequest($request);

    expect($pendingRequest->getAuthenticator())->toEqual(new TokenAuthenticator('ooh-this-is-cool'));
    expect($pendingRequest->headers()->get('Authorization'))->toEqual('Bearer ooh-this-is-cool');
});

test('you can add an authenticator inside of request middleware', function () {
    $request = new UserRequest;

    $request->middleware()->onRequest(function (PendingRequest $pendingRequest) {
        return $pendingRequest->withTokenAuth('yee-haw-request');
    });

    $pendingRequest = connector()->createPendingRequest($request);

    expect($pendingRequest->headers()->get('Authorization'))->toEqual('Bearer yee-haw-request');
});

test('if you use the authenticate method on a fully constructed pending request it will authenticate right away', function () {
    $connector = new TestConnector();
    $pendingRequest = $connector->createPendingRequest(new UserRequest);

    expect($pendingRequest->headers()->all())->toEqual([
        'Accept' => 'application/json',
    ]);

    $pendingRequest->authenticate(new TokenAuthenticator('yee-haw-request'));

    expect($pendingRequest->headers()->all())->toEqual([
        'Accept' => 'application/json',
        'Authorization' => 'Bearer yee-haw-request',
    ]);
});
