<?php

declare(strict_types=1);

use Saloon\Debugging\Debugger;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Requests\HasMultipartBodyRequest;

test('it works', function () {
    $connector = new TestConnector;
    $request = new HasMultipartBodyRequest();

    $connector->debug(function (Debugger $debugger) {
        $debugger->beforeAndAfterSent();

        $debugger->usingDriver('ray');
    });

    $connector->send($request);
});
