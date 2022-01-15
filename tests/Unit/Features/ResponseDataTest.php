<?php

use Sammyjo20\Saloon\Managers\RequestManager;
use Sammyjo20\Saloon\Tests\Resources\Requests\PostJsonRequest;
use Sammyjo20\Saloon\Tests\Resources\Requests\PostRequest;

test('a request with the hasJsonBody feature sends the json data', function () {
    $request = new PostJsonRequest();
    $request->addData('name', 'Sam');

    $requestManager = new RequestManager($request);

    $requestManager->prepareMessage();

    $config = $requestManager->getConfig();

    expect($config)->toHaveKey('json');
    expect($config['json'])->toHaveKey('foo', 'bar'); // Test Default
    expect($config['json'])->toHaveKey('name', 'Sam'); // Test Adding Data After
});

test('if setData is used, all other default data wont be included', function () {
    $request = new PostJsonRequest();

    $request->setData([
        'overwritten' => true,
    ]);

    $requestManager = new RequestManager($request);

    $requestManager->prepareMessage();

    $config = $requestManager->getConfig();

    expect($config)->toHaveKey('json');
    expect($config['json'])->toEqual(['overwritten' => true]);
});

test('a connector can have jsonBody that is set', function () {
    $request = new PostRequest();

    $request->setData([
        'abc' => 'def',
    ]);

    $requestManager = new RequestManager($request);

    $requestManager->prepareMessage();

    $config = $requestManager->getConfig();

    dd($config);

    expect($config)->toHaveKey('json');
    expect($config['json'])->toHaveKey('connectorId', '1'); // Test Default
});
