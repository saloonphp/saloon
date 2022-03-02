<?php

use Sammyjo20\Saloon\Tests\Fixtures\Requests\UserRequest;
use Sammyjo20\Saloon\Tests\Fixtures\Keychains\AuthKeychain;
use Sammyjo20\Saloon\Tests\Fixtures\Connectors\TestConnector;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\KeychainRequest;
use Sammyjo20\Saloon\Tests\Fixtures\Keychains\AdvancedKeychain;
use Sammyjo20\Saloon\Tests\Fixtures\Connectors\KeychainConnector;

test('a request can have a default keychain that is populated before the request is sent', function () {
    $request = new KeychainRequest(1, 2);
    $requestManager = $request->getRequestManager();

    $requestManager->hydrate();

    $headers = $requestManager->getHeaders();

    // The auth keychain's "boot" method should have run which would mean the value is 12345.

    expect($headers)->toHaveKey('Authorization', 'Bearer 12345');
    expect($requestManager)->getRequest()->getLoadedKeychain()->toBeInstanceOf(AuthKeychain::class);
});

test('a request can use its connectors default keychain if there is no default populated on the request', function () {
    $request = (new UserRequest)->setConnector(new KeychainConnector);
    $requestManager = $request->getRequestManager();

    $requestManager->hydrate();

    $headers = $requestManager->getHeaders();

    // The auth keychain's "boot" method should have run which would mean the value is 12345.

    expect($headers)->toHaveKey('Authorization', 'Bearer 12345');
    expect($headers)->toHaveKey('X-API-Key', 'my-api-key');

    expect($requestManager)->getRequest()->getLoadedKeychain()->toBeInstanceOf(AdvancedKeychain::class);
});

test('if both the connector and the request have a default keychain, the request will take priority', function () {
    $request = (new KeychainRequest())->setConnector(new KeychainConnector);
    $requestManager = $request->getRequestManager();

    $requestManager->hydrate();

    $headers = $requestManager->getHeaders();

    // The auth keychain's "boot" method should have run which would mean the value is 12345.

    expect($headers)->toHaveKey('Authorization', 'Bearer 12345');
    expect($requestManager)->getRequest()->getLoadedKeychain()->toBeInstanceOf(AuthKeychain::class);
});

test('you can load a keychain onto a request before it is sent', function () {
    $request = (new UserRequest)->authenticate(new AuthKeychain('custom-user-token'));
    $requestManager = $request->getRequestManager();

    $requestManager->hydrate();

    $headers = $requestManager->getHeaders();

    // The auth keychain's "boot" method should have run which would mean the value is 12345.

    expect($headers)->toHaveKey('Authorization', 'Bearer custom-user-token');
    expect($requestManager)->getRequest()->getLoadedKeychain()->toBeInstanceOf(AuthKeychain::class);
});

test('you can load a keychain onto a connector before it is sent', function () {
    $connector = TestConnector::make()->authenticate(new AuthKeychain('custom-user-token'));

    $request = (new UserRequest)->setConnector($connector);
    $requestManager = $request->getRequestManager();

    $requestManager->hydrate();

    $headers = $requestManager->getHeaders();

    // The auth keychain's "boot" method should have run which would mean the value is 12345.

    expect($headers)->toHaveKey('Authorization', 'Bearer custom-user-token');
    expect($requestManager)->getRequest()->getLoadedKeychain()->toBeInstanceOf(AuthKeychain::class);
});
