<?php

declare(strict_types=1);

use Saloon\MockConfig;
use League\Flysystem\Filesystem;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Exceptions\FixtureMissingException;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use League\Flysystem\Local\LocalFilesystemAdapter;

afterEach(function () {
    MockConfig::setFixturePath('tests/Fixtures/Saloon');
});

test('you can change the default fixture path', function () {
    expect(MockConfig::getFixturePath())->toEqual('tests/Fixtures/Saloon');

    MockConfig::setFixturePath('saloon-requests/responses');

    expect(MockConfig::getFixturePath())->toEqual('saloon-requests/responses');
});

test('you can throw an exception if the fixture does not exist', function () {
    MockConfig::setFixturePath('tests/Fixtures/Saloon');

    expect(MockConfig::isThrowingOnMissingFixtures())->toBeFalse();

    MockConfig::throwOnMissingFixtures();

    $mockClient = new MockClient([
        MockResponse::fixture('example'),
    ]);

    connector()->send(new UserRequest, $mockClient);
})->throws(FixtureMissingException::class, 'The fixture "example.json" could not be found in storage.');

test('if the default fixture path doesnt exist it will be created', function () {
    $filesystem = new Filesystem(new LocalFilesystemAdapter('tests/Fixtures'));
    $filesystem->deleteDirectory('OtherFixturePath');

    MockConfig::setFixturePath('tests/Fixtures/OtherFixturePath');

    expect($filesystem->has('OtherFixturePath'))->toBeFalse();

    new MockClient([
        MockResponse::fixture('example'),
    ]);

    expect($filesystem->has('OtherFixturePath'))->toBeTrue();
});
