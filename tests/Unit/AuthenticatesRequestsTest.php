<?php

declare(strict_types=1);

use Saloon\Tests\Fixtures\Requests\UserRequest;

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

test('you can add a token to a query parameter', function () {
    $request = UserRequest::make()->withQueryAuth('token', 'Sammyjo20');

    $pendingRequest = connector()->createPendingRequest($request);
    $query = $pendingRequest->query()->all();

    expect($query)->toHaveKey('token', 'Sammyjo20');
});
