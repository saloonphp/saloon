<?php

declare(strict_types=1);

use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Saloon\Debugging\Debugger;
use Saloon\Debugging\Drivers\RayDebugger;
use Saloon\Debugging\Drivers\StreamDebugger;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Debuggers\FakeRay;
use Saloon\Tests\Fixtures\Requests\UserRequest;

test('you can debug using the ray driver', function () {
    $fakeRay = new FakeRay();

    RayDebugger::setRay($fakeRay);

    $mockClient = new MockClient([
        new MockResponse(['name' => 'Sam'], 200, ['X-Foo' => 'Bar'])
    ]);

    $connector = new TestConnector;
    $connector->withMockClient($mockClient);

    $connector->debug(function (Debugger $debugger) {
        $debugger->showRequestAndResponse()->usingDriver('ray');
    });

    $connector->send(new UserRequest);

    // TODO:
    // Not sure how to fake ray. I've tried making a FakeRay client
    // but it's not working as the content value is completely encoded
})->skip('TODO');

test('you can debug using the syslog driver', function () {
    //
})->skip('TOOD');

test('you can debug using the error log driver', function () {
    //
})->skip('TOOD');

test('you can debug the stream driver', function (mixed $resource) {
    $filesystem = new Filesystem(new LocalFilesystemAdapter('tests/Fixtures'));
    $filesystem->write('Saloon/Testing/debug.txt', '');

    fwrite(is_string($resource) ? fopen($resource, 'wb') : $resource, '');

    $mockClient = new MockClient([
        new MockResponse(['name' => 'Sam'], 200, ['X-Foo' => 'Bar'])
    ]);

    $connector = new TestConnector;
    $connector->withMockClient($mockClient);

    $connector->debug(function (Debugger $debugger) use ($resource) {
        $debugger->showRequestAndResponse()->usingDriver(new StreamDebugger($resource));
    });

    $connector->send(new UserRequest);

    // We'll use Flysystem to help us find the file

    expect(
        $filesystem->read('Debuggers/FileDebugExample.txt')
    )->toEqual(
        $filesystem->read('Saloon/Testing/debug.txt')
    );
})->with([
    fn () => fopen('tests/Fixtures/Saloon/Testing/debug.txt', 'wb'),
    fn () => 'tests/Fixtures/Saloon/Testing/debug.txt',
]);
