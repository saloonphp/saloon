<?php

use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Helpers\FixtureRecorder;
use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\UserRequest;

$filesystem = new Filesystem(new LocalFilesystemAdapter(fixtureRecorderPath()));

beforeEach(function () use ($filesystem) {
    $filesystem->deleteDirectory('/');
    $filesystem->createDirectory('/');
});

test('you can configure the directory the recorder will store fixtures in', function () {
    $recorder = new FixtureRecorder(fixtureRecorderPath());

    // We will also check that it cleans up the directory.

    expect($recorder->getFixtureDirectory())->toEqual(fixtureRecorderPath());
});

test('it will throw an exception if the path provided is not a directory', function () {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('The provided fixture directory is not a valid directory.');

    new FixtureRecorder('DOESNOTEXIST/');
});

test('the fixtures mock response contains the correct structure with headers, status and body', function () use ($filesystem) {
    $recorder = new FixtureRecorder(fixtureRecorderPath());

    expect($filesystem->fileExists('UserRequest.json'))->toBeFalse();

    $mockResponse = new MockResponse('{"name":"Sammy"}', 202, ['X-Foo' => 'Bar']);

    $mockClient = new MockClient([
        $mockResponse
    ]);

    $userRequest = new UserRequest();
    $userRequest->withMockClient($mockClient);

    $recorder->record($userRequest, null, 'UserRequest');

    expect($filesystem->fileExists('UserRequest.json'))->toBeTrue();

    $data = $filesystem->read('UserRequest.json');

    expect($data)->toBeString();

    $decoded = json_decode($data, true);

    expect($decoded)->toHaveKey('mockResponse');

    $unserialized = unserialize($decoded['mockResponse']);

    expect($unserialized)->toEqual($mockResponse);
});

test('you can retrieve the fixture directory from the fixture recorder', function () {
    $recorder = new FixtureRecorder(fixtureRecorderPath());

    expect($recorder->getFixtureDirectory())->toEqual(fixtureRecorderPath());
});

test('you can enable and disable recording failed responses', function () {
    $recorder = new FixtureRecorder(fixtureRecorderPath());

    expect($recorder->isRecordingFailures())->toBeFalse();

    $recorder->recordFailures();

    expect($recorder->isRecordingFailures())->toBeTrue();

    $recorder->doNotRecordFailures();

    expect($recorder->isRecordingFailures())->toBeFalse();
});
