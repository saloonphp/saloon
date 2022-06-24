<?php

use Sammyjo20\Saloon\Managers\RequestManager;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\SubRequest;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\UserRequestWithBootPlugin;

test('a plugin boot method has access to the request', function () {
    $requestManager = new RequestManager(new UserRequestWithBootPlugin(1, 2));
    $requestManager->hydrate();

    expect($requestManager->getHeaders())->toHaveKey('X-Plugin-User-Id', 1);
    expect($requestManager->getHeaders())->toHaveKey('X-Plugin-Group-Id', 2);
});

test('sub-request does not need to use plugins', function () {
    $requestManager = new RequestManager(new SubRequest(1, 2));
    $requestManager->hydrate();

    expect($requestManager->getHeaders())->toHaveKey('X-Plugin-User-Id', 1);
    expect($requestManager->getHeaders())->toHaveKey('X-Plugin-Group-Id', 2);
});
