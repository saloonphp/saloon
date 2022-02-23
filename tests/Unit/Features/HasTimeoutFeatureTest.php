<?php

use Sammyjo20\Saloon\Managers\RequestManager;
use Sammyjo20\Saloon\Tests\Resources\Connectors\TimeoutConnector;
use Sammyjo20\Saloon\Tests\Resources\Requests\TimeoutRequest;
use Sammyjo20\Saloon\Tests\Resources\Requests\UserRequest;

test('a request is given a default timeout and connect timeout', function () {
    $requestManager = new RequestManager(new TimeoutRequest);
    $requestManager->hydrate();

    $config = $requestManager->getConfig();

    expect($config)->toHaveKey('connect_timeout', 30);
    expect($config)->toHaveKey('timeout', 30);
});

test('a connector is given a default timeout and connect timeout', function () {
    $request = (new UserRequest)->setLoadedConnector(new TimeoutConnector);

    $requestManager = new RequestManager($request);
    $requestManager->hydrate();

    $config = $requestManager->getConfig();

    expect($config)->toHaveKey('connect_timeout', 10);
    expect($config)->toHaveKey('timeout', 5);
});
