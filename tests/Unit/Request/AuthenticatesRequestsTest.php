<?php

declare(strict_types=1);

use GuzzleHttp\RequestOptions;
use Saloon\Exceptions\SaloonException;
use Saloon\Http\Auth\NullAuthenticator;
use Saloon\Http\Auth\MultiAuthenticator;
use Saloon\Http\Auth\TokenAuthenticator;
use Saloon\Http\Auth\HeaderAuthenticator;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Connectors\ArraySenderConnector;
use Saloon\Tests\Fixtures\Connectors\DefaultAuthenticatorConnector;

test('you can add basic auth to a request', function () {
    $request = new UserRequest;
    $request->withBasicAuth('Sammyjo20', 'Cowboy1');

    $pendingRequest = connector()->createPendingRequest($request);
    $headers = $pendingRequest->headers()->all();

    expect($headers)->toBeArray();
    expect($headers['Authorization'])->toEqual('Basic ' . base64_encode('Sammyjo20:Cowboy1'));
});

test('you can attach an authorization token to a request', function () {
    $request = UserRequest::make()->withTokenAuth('Sammyjo20');

    $pendingRequest = connector()->createPendingRequest($request);
    $headers = $pendingRequest->headers()->all();

    expect($headers)->toHaveKey('Authorization', 'Bearer Sammyjo20');
});

test('you can add digest auth to a request', function () {
    $request = new UserRequest;
    $request->withDigestAuth('Sammyjo20', 'Cowboy1', 'Howdy');

    $pendingRequest = connector()->createPendingRequest($request);
    $config = $pendingRequest->config()->all();

    expect($config['auth'])->toBeArray();
    expect($config['auth'][0])->toEqual('Sammyjo20');
    expect($config['auth'][1])->toEqual('Cowboy1');
    expect($config['auth'][2])->toEqual('Howdy');

    // We'll now test trying to use the `withDigestAuth` on the array sender

    $arraySenderConnector = new ArraySenderConnector;
    $arraySenderConnector->send($request);
})->throws(SaloonException::class, 'The DigestAuthenticator is only supported when using the GuzzleSender.');

test('you can add a token to a query parameter', function () {
    $request = UserRequest::make()->withQueryAuth('token', 'Sammyjo20');

    $pendingRequest = connector()->createPendingRequest($request);
    $query = $pendingRequest->query()->all();

    expect($query)->toHaveKey('token', 'Sammyjo20');
});

test('you can add a header to a request', function () {
    $request = UserRequest::make()->withHeaderAuth('Sammyjo20', 'X-Authorization');

    $pendingRequest = connector()->createPendingRequest($request);
    $query = $pendingRequest->headers()->all();

    expect($query)->toHaveKey('X-Authorization', 'Sammyjo20');
});

test('you can add a certificate to a request', function () {
    $certPath = __DIR__ . '/certificate.cer';

    $requestA = UserRequest::make()->withCertificateAuth($certPath);

    $pendingRequestA = connector()->createPendingRequest($requestA);
    $configA = $pendingRequestA->config()->all();

    expect($configA)->toBe([
        RequestOptions::CERT => $certPath,
    ]);

    // Test with password

    $requestB = UserRequest::make()->withCertificateAuth($certPath, 'example');

    $pendingRequestB = connector()->createPendingRequest($requestB);
    $configB = $pendingRequestB->config()->all();

    expect($configB)->toBe([
        RequestOptions::CERT => [$certPath, 'example'],
    ]);
});

test('you can use multiple authenticators at the same time using the defaultAuth method', function () {
    $request = UserRequest::make()->authenticate(new MultiAuthenticator(
        new TokenAuthenticator('example'),
        new HeaderAuthenticator('api-key', 'X-API-Key'),
    ));

    $pendingRequest = connector()->createPendingRequest($request);

    $headers = $pendingRequest->headers()->all();

    expect($headers)->toEqual([
        'Accept' => 'application/json',
        'Authorization' => 'Bearer example',
        'X-API-Key' => 'api-key',
    ]);
});

test('you can use a null authenticator to disable default authentication entirely', function () {
    $connector = new DefaultAuthenticatorConnector;
    $request = new UserRequest;

    $request->authenticate(new NullAuthenticator);

    $pendingRequest = $connector->createPendingRequest($request);

    expect($pendingRequest->headers()->all())->toEqual(['Accept' => 'application/json']);
});
