<?php

declare(strict_types=1);

use Saloon\Debugging\Debugger;
use Saloon\Debugging\DebugData;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Senders\GuzzleSender;
use Saloon\Debugging\Drivers\RayDebugger;
use Saloon\Debugging\Drivers\ErrorLogDebugger;
use Saloon\Debugging\Drivers\SystemLogDebugger;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Debuggers\ArrayDebugger;
use Saloon\Tests\Fixtures\Connectors\TestConnector;

test('the debugger is instantiated with some default debugging drivers', function () {
    $connector = new TestConnector;
    $debugger = $connector->debug();

    expect($debugger)->toBeInstanceOf(Debugger::class);

    $drivers = $debugger->getRegisteredDrivers();

    expect($drivers)->toHaveKey('ray', new RayDebugger);
    expect($drivers)->toHaveKey('error_log', new ErrorLogDebugger);
    expect($drivers)->toHaveKey('syslog', new SystemLogDebugger);
});

test('the debug data can access the underlying pending request and response', function () {
    $mockClient = new MockClient([
        new MockResponse(['name' => 'Sam'], 200),
    ]);

    $connector = new TestConnector;
    $request = new UserRequest;

    $response = $connector->send($request, $mockClient);
    $pendingRequest = $response->getPendingRequest();

    $debugData = new DebugData($pendingRequest, $response);

    expect($debugData->getSender())->toBeInstanceOf(GuzzleSender::class);
    expect($debugData->getPendingRequest())->toBe($pendingRequest);
    expect($debugData->getResponse())->toBe($response);
    expect($debugData->getConnector())->toBe($connector);
    expect($debugData->getRequest())->toBe($request);
    expect($debugData->getUrl())->toEqual($pendingRequest->getUrl());
    expect($debugData->getMethod())->toEqual($pendingRequest->getMethod());
    expect($debugData->getStatusCode())->toEqual($response->status());

    expect($debugData->wasSent())->toBeTrue();
    expect($debugData->wasNotSent())->toBeFalse();
});

test('you can register global drivers', function () {
    $connector = new TestConnector;
    $debugger = $connector->debug();

    $arrayDebugger = new ArrayDebugger;

    Debugger::registerGlobalDriver($arrayDebugger);

    expect($debugger->getRegisteredDrivers())->toHaveKey('array', $arrayDebugger);
});
