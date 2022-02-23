<?php

use Sammyjo20\Saloon\Managers\RequestManager;
use Sammyjo20\Saloon\Tests\Resources\Requests\NewPluginBootSyntaxRequest;
use Sammyjo20\Saloon\Tests\Resources\Requests\UserRequestWithBootPlugin;

test('a plugin boot method has access to the request', function () {
    $requestManager = new RequestManager(new UserRequestWithBootPlugin(1, 2));
    $requestManager->hydrate();

    expect($requestManager->getHeaders())->toHaveKey('X-Plugin-User-Id', 1);
    expect($requestManager->getHeaders())->toHaveKey('X-Plugin-Group-Id', 2);
});

test('a plugin can be booted with the new boot syntax', function () {
    $requestManager = new RequestManager(new NewPluginBootSyntaxRequest);
    $requestManager->hydrate();

    expect($requestManager->getHeaders())->toHaveKey('X-New-Boot-Syntax', 'Yes');
});
