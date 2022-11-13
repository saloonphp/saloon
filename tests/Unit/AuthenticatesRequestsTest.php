<?php declare(strict_types=1);

use Saloon\Tests\Fixtures\Requests\UserRequest;

test('you can add basic auth to a request', function () {
    $request = new UserRequest;
    $request->withBasicAuth('Sammyjo20', 'Cowboy1');

    $pendingRequest = $request->createPendingRequest();
    $config = $pendingRequest->config()->all();

    expect($config['auth'])->toBeArray();
    expect($config['auth'][0])->toEqual('Sammyjo20');
    expect($config['auth'][1])->toEqual('Cowboy1');
});

test('you can add digest auth to a request', function () {
    $request = new UserRequest;
    $request->withDigestAuth('Sammyjo20', 'Cowboy1', 'Howdy');

    $pendingRequest = $request->createPendingRequest();
    $config = $pendingRequest->config()->all();

    expect($config['auth'])->toBeArray();
    expect($config['auth'][0])->toEqual('Sammyjo20');
    expect($config['auth'][1])->toEqual('Cowboy1');
    expect($config['auth'][2])->toEqual('Howdy');
});

test('you can attach an authorization token to a request', function () {
    $request = UserRequest::make()->withTokenAuth('Sammyjo20');

    $pendingRequest = $request->createPendingRequest();
    $headers = $pendingRequest->headers()->all();

    expect($headers)->toHaveKey('Authorization', 'Bearer Sammyjo20');
});

test('you can add a token to a query parameter', function () {
    $request = UserRequest::make()->withQueryAuth('token', 'Sammyjo20');

    $pendingRequest = $request->createPendingRequest();
    $query = $pendingRequest->queryParameters()->all();

    expect($query)->toHaveKey('token', 'Sammyjo20');
});
