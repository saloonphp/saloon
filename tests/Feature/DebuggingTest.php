<?php

use Saloon\Debugging\Debugger;
use Saloon\Debugging\Drivers\RayDebugger;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Requests\HasMultipartBodyRequest;
use Saloon\Tests\Fixtures\Requests\UserRequest;

test('it works', function () {
    $connector = new TestConnector;
    $request = new HasMultipartBodyRequest();

    $connector->debug(function (Debugger $debugger) {
        $debugger->beforeAndAfterSent();

        $debugger->usingDriver('ray');
    });

    $connector->send($request);
});
