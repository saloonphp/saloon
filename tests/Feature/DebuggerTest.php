<?php

declare(strict_types=1);

use Saloon\Debugging\Debugger;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Requests\HasJsonBodyRequest;

test('it works', function () {
    $connector = new TestConnector;

    $connector->debug(function (Debugger $debugger) {
        $debugger->showResponse()->usingDriver('ray');
    });

    $response = $connector->send(new HasJsonBodyRequest());
});

test('if the debugger has been configured to run before sent it will log the request', function () {
    //
});

test('if the debugger has been configured to run after sent it will log the response', function () {
    //
});
