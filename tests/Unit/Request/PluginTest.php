<?php

declare(strict_types=1);

use Saloon\Tests\Fixtures\Requests\SubRequest;
use Saloon\Tests\Fixtures\Requests\UserRequestWithBootPlugin;

test('a plugin boot method has access to the request', function () {
    $request = new UserRequestWithBootPlugin(1, 2);

    $pendingRequest = connector()->createPendingRequest($request);
    $headers = $pendingRequest->headers()->all();

    expect($headers)->toHaveKey('X-Plugin-User-Id', 1);
    expect($headers)->toHaveKey('X-Plugin-Group-Id', 2);
});

test('sub-request does not need to use plugins', function () {
    $request = new SubRequest(1, 2);

    $pendingRequest = connector()->createPendingRequest($request);
    $headers = $pendingRequest->headers()->all();

    expect($headers)->toHaveKey('X-Plugin-User-Id', 1);
    expect($headers)->toHaveKey('X-Plugin-Group-Id', 2);
});
