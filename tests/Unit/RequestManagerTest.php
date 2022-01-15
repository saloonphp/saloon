<?php

use Sammyjo20\Saloon\Managers\RequestManager;
use Sammyjo20\Saloon\Tests\Resources\Requests\HeaderRequest;
use Sammyjo20\Saloon\Tests\Resources\Requests\ReplaceConfigRequest;
use Sammyjo20\Saloon\Tests\Resources\Requests\ReplaceHeaderRequest;

test('a request is built up correctly', function () {
    $requestManager = new RequestManager(new HeaderRequest());

    // Manually prepare the message

    $requestManager->prepareMessage();

    expect($requestManager->getHeaders())->toEqual([
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
        'X-Connector-Header' => 'Sam', // Added by connector
        'X-Custom-Header' => 'Howdy', // Added by request
    ]);

    expect($requestManager->getConfig())->toEqual([
        'json' => [
            'foo' => 'bar' // Added by feature
        ],
        'http_errors' => false, // Added manually in connector
        'timeout' => 5, // Added manually in request
        'debug' => true // Added by connector feature
    ]);
});

test('a request headers replace connectors headers', function () {
    $requestManager = new RequestManager(new ReplaceHeaderRequest());

    $requestManager->prepareMessage();

    expect($requestManager->getHeaders())->toHaveKey('X-Connector-Header', 'Howdy');
});

test('a request config replace connectors config', function () {
    $requestManager = new RequestManager(new ReplaceConfigRequest());

    $requestManager->prepareMessage();

    expect($requestManager->getConfig())->toHaveKey('debug', false);
});
