<?php

declare(strict_types=1);

// TODO: Test each debugging driver works

use Saloon\Debugging\Drivers\FileDebugger;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Requests\UserRequest;

test('you can debug the file driver', function () {
    $connector = new TestConnector;

    $connector->debug()
        ->showRequestAndResponse()
        ->usingDriver(new FileDebugger(
            resource: fopen('tests/Fixtures/Saloon/Testing/debug.txt', 'wb')
        ));

    $connector->send(new UserRequest);
});

test('you can debug the flysystem driver', function () {
    //
});
