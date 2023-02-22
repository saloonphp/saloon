<?php

use Saloon\Debugging\Debugger;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Requests\UserRequest;

test('it works', function () {
    $connector = new TestConnector;

    $connector->debug()->showRequest()->usingDriver('ray');

    $response = $connector->send(new UserRequest);
});

test('if the debugger has been configured to run before sent it will log the request', function () {
    //
});

test('if the debugger has been configured to run after sent it will log the response', function () {
    //
});
