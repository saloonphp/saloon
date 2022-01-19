<?php

use Sammyjo20\Saloon\Managers\RequestManager;
use Sammyjo20\Saloon\Tests\Resources\Requests\PostRequest;
use Sammyjo20\Saloon\Tests\Resources\Requests\PostJsonRequest;
use Sammyjo20\Saloon\Tests\Resources\Requests\OverwrittenPostRequest;
use Sammyjo20\Saloon\Tests\Resources\Requests\PostConnectorDataBlankRequest;

test('a request with the hasJsonBody feature sends the json data', function () {
    $request = new PostJsonRequest();
    $request->addData('name', 'Sam');

    $requestManager = new RequestManager($request);

    $requestManager->hydrate();

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

    $requestManager->hydrate();

    $config = $requestManager->getConfig();

    expect($config)->toHaveKey('json');
    expect($config['json'])->toEqual(['overwritten' => true]);
});

test('a connector can have jsonBody that is set', function () {
    $request = new PostRequest();

    $requestManager = new RequestManager($request);

    $requestManager->hydrate();

    $config = $requestManager->getConfig();

    expect($config)->toHaveKey('json');
    expect($config['json'])->toHaveKey('connectorId', '1'); // Test Default
    expect($config['json'])->toHaveKey('requestId', '2'); // Test Default
});

test('a request can overwrite all jsonBody', function () {
    $request = new PostRequest();

    $request->setData([
        'customData' => 'hello!',
    ]);

    $requestManager = new RequestManager($request);

    $requestManager->hydrate();

    $config = $requestManager->getConfig();

    expect($config)->toHaveKey('json');
    expect($config['json'])->toEqual(['customData' => 'hello!']);
});

test('request form data can overwrite a connectors form data', function () {
    $request = new OverwrittenPostRequest();

    $requestManager = new RequestManager($request);

    $requestManager->hydrate();

    $config = $requestManager->getConfig();

    expect($config)->toHaveKey('json');
    expect($config['json'])->toEqual(['connectorId' => 2]);
});

test('manually overwriting form data in runtime can overwrite connectors form data', function () {
    $request = new PostConnectorDataBlankRequest();

    $request->addData('connectorId', 3);

    $requestManager = new RequestManager($request);

    $requestManager->hydrate();

    $config = $requestManager->getConfig();

    expect($config)->toHaveKey('json');
    expect($config['json'])->toEqual(['connectorId' => 3]);
});

test('manually overwriting form data in runtime can overwrite request form data', function () {
    $request = new PostRequest();

    $request->addData('requestId', 4);

    $requestManager = new RequestManager($request);

    $requestManager->hydrate();

    $config = $requestManager->getConfig();

    expect($config)->toHaveKey('json');
    expect($config['json'])->toEqual(['requestId' => 4, 'connectorId' => 1]);
});
