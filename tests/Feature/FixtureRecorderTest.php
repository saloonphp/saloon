<?php

use League\Flysystem\Filesystem;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Helpers\FixtureRecorder;
use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\Saloon\Tests\Fixtures\Connectors\TestConnector;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\ErrorRequest;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\UserRequest;

$filesystem = new Filesystem(new LocalFilesystemAdapter(fixtureRecorderPath()));

beforeEach(function () use ($filesystem) {
    $filesystem->deleteDirectory('/');
    $filesystem->createDirectory('/');
});

test('it will record a response and store it in the fixture directory', function () use ($filesystem) {
    $recorder = new FixtureRecorder(fixtureRecorderPath());

    expect($filesystem->fileExists('UserRequest.json'))->toBeFalse();

    $userRequestA = new UserRequest();
    $responseA = $recorder->record($userRequestA);

    expect($responseA->isMocked())->toBeFalse();

    $userRequestB = new UserRequest();
    $responseB = $recorder->record($userRequestB);

    expect($responseB->isMocked())->toBeTrue();
    expect($filesystem->fileExists('UserRequest.json'))->toBeTrue();
});

test('if a connector is provided it will send the request through the connector', function () use ($filesystem) {
    $recorder = new FixtureRecorder(fixtureRecorderPath());

    $mockClient = new MockClient([new MockResponse(['name' => 'Sammy'])]);

    $connector = new TestConnector;
    $connector->uniqueReference = 'YeeHaw!';
    $connector->withMockClient($mockClient);

    $userRequest = new UserRequest();
    $response = $recorder->record($userRequest, $connector);

    expect($response->getOriginalRequest()->getConnector())->toBe($connector);
    expect($response->json())->toEqual(['name' => 'Sammy']);
    expect($filesystem->fileExists('UserRequest.json'))->toBeTrue();
});

test('a custom name can be provided for the fixture', function () use ($filesystem) {
    $recorder = new FixtureRecorder(fixtureRecorderPath());

    expect($filesystem->fileExists('CowBoy.json'))->toBeFalse();

    $mockClient = new MockClient([new MockResponse(['name' => 'Sammy'])]);

    $userRequest = new UserRequest();
    $userRequest->withMockClient($mockClient);

    $recorder->record($userRequest, null,'CowBoy');

    expect($filesystem->fileExists('CowBoy.json'))->toBeTrue();
});

test('the fixture recorder will not record on failures unless requested', function () use ($filesystem) {
    $recorder = new FixtureRecorder(fixtureRecorderPath());

    expect($filesystem->fileExists('ErrorRequest.json'))->toBeFalse();

    $mockClient = new MockClient([
        ErrorRequest::class => new MockResponse(['error' => 'Unexpected Error!'], 500)
    ]);

    $errorRequestA = new ErrorRequest();
    $errorRequestA->withMockClient($mockClient);

    $recorder->record($errorRequestA);

    expect($filesystem->fileExists('ErrorRequest.json'))->toBeFalse();

    // Now we will request it.

    $recorder->recordFailures();

    $errorRequestB = new ErrorRequest();
    $errorRequestB->withMockClient($mockClient);

    $recorder->record($errorRequestA);

    expect($filesystem->fileExists('ErrorRequest.json'))->toBeTrue();
});
