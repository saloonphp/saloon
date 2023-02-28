<?php

declare(strict_types=1);

use Saloon\Debugging\Debugger;
use League\Flysystem\Filesystem;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Debugging\Drivers\RayDebugger;
use Saloon\Debugging\Drivers\StreamDebugger;
use Saloon\Tests\Fixtures\Debuggers\FakeRay;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Saloon\Tests\Fixtures\Connectors\TestConnector;

test('you can debug using the ray driver', function () {
    $fakeRay = new FakeRay();

    RayDebugger::setRay($fakeRay);

    $mockClient = new MockClient([
        new MockResponse(['name' => 'Sam'], 200, ['X-Foo' => 'Bar']),
    ]);

    $connector = new TestConnector;
    $connector->withMockClient($mockClient);

    $connector->debug(function (Debugger $debugger) {
        $debugger->showRequestAndResponse()->usingDriver('ray');
    });

    expect($fakeRay->getSentRequests())->toHaveCount(0);

    $connector->send(new UserRequest);

    $sentRequests = $fakeRay->getSentRequests();

    expect($sentRequests)->toHaveCount(4);

    expect($sentRequests[0]['payloads'][0]['type'])->toEqual('log');

    expect($sentRequests[1]['payloads'][0]['type'])->toEqual('label');
    expect($sentRequests[1]['payloads'][0]['content'])->toEqual(['label' => 'Saloon Debugger']);

    expect($sentRequests[2]['payloads'][0]['type'])->toEqual('log');

    expect($sentRequests[3]['payloads'][0]['type'])->toEqual('label');
    expect($sentRequests[3]['payloads'][0]['content'])->toEqual(['label' => 'Saloon Debugger']);
});

test('you can debug using the syslog driver', function () {
    $mockClient = new MockClient([
        new MockResponse(['name' => 'Sam'], 200, ['X-Foo' => 'Bar']),
    ]);

    $connector = new TestConnector;
    $connector->withMockClient($mockClient);

    $connector->debug(function (Debugger $debugger) {
        $debugger->showRequestAndResponse()->usingDriver('syslog');
    });

    $connector->send(new UserRequest);

    $mockClient->assertSentCount(1);
});

test('you can debug using the error log driver', function () {
    $mockClient = new MockClient([
        new MockResponse(['name' => 'Sam'], 200, ['X-Foo' => 'Bar']),
    ]);

    $connector = new TestConnector;
    $connector->withMockClient($mockClient);

    $connector->debug(function (Debugger $debugger) {
        $debugger->showRequestAndResponse()->usingDriver('error_log');
    });

    $connector->send(new UserRequest);

    $mockClient->assertSentCount(1);
});
