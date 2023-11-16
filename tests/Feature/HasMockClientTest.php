<?php

declare(strict_types=1);

use Saloon\MockConfig;
use Saloon\Http\PendingRequest;
use League\Flysystem\Filesystem;
use Saloon\Http\Faking\MockResponse;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Requests\SoloUserRequest;
use Saloon\Tests\Fixtures\Requests\UserRequestWithFakeData;
use Saloon\Tests\Fixtures\Requests\UserRequestWithFakeDataFixture;

$filesystem = new Filesystem(new LocalFilesystemAdapter('tests/Fixtures/Saloon/Testing'));

beforeEach(function () use ($filesystem) {
    MockConfig::setFixturePath('tests/Fixtures/Saloon/Testing');

    $filesystem->deleteDirectory('/');
    $filesystem->createDirectory('/');
});

afterEach(function () {
    MockConfig::setFixturePath('tests/Fixtures/Saloon');
});

it('can mock a request with a string', function () {
    $connector = TestConnector::make()->withRequestMocks([
        UserRequest::class => 'Sam',
    ]);

    $response = $connector->send(new UserRequest);
    expect($response->body())->toEqual('Sam');
});

it('can mock a request with an array', function () {
    $connector = TestConnector::make()->withRequestMocks([
        UserRequest::class => ['Sam'],
    ]);

    $response = $connector->send(new UserRequest);
    expect($response->json())->toEqual(['Sam']);
});

it('can mock a request with a MockResponse instance', function () {
    $connector = TestConnector::make()->withRequestMocks([
        UserRequest::class => MockResponse::make(['Sam']),
    ]);

    $response = $connector->send(new UserRequest);
    expect($response->json())->toEqual(['Sam']);
});

it('can mock a request with a Fixture instance', function () {
    $connector = TestConnector::make()->withRequestMocks([
        UserRequest::class => MockResponse::fixture('user'),
    ]);

    $response = $connector->send(new UserRequest);
    expect($response->json())->toEqual([
        'name' => 'Sammyjo20',
        'actual_name' => 'Sam',
        'twitter' => '@carre_sam',
    ]);
});

it('can mock a request with a callback', function () {
    $connector = TestConnector::make()->withRequestMocks([
        UserRequest::class => fn (PendingRequest $pendingRequest) => MockResponse::fixture('user'),
    ]);

    $response = $connector->send(new UserRequest);
    expect($response->json())->toEqual([
        'name' => 'Sammyjo20',
        'actual_name' => 'Sam',
        'twitter' => '@carre_sam',
    ]);
});

it('can mock a URL instead of a request class', function () {
    $connector = TestConnector::make()->withRequestMocks([
        '/user' => ['Sam'],
    ]);

    $response = $connector->send(new UserRequest);
    expect($response->json())->toEqual(['Sam']);
});


test('a request can implement the HasFakeData interface that returns an array', function () {
    $connector = TestConnector::make()->withRequestMocks([
        UserRequestWithFakeData::class,
    ]);

    $response = $connector->send(new UserRequestWithFakeData);
    expect($response->json())->toEqual(['Sam']);
});

test('a request can implement the HasFakeData interface that returns a Fixture', function () {
    $connector = TestConnector::make()->withRequestMocks([
        UserRequestWithFakeDataFixture::class,
    ]);

    $response = $connector->send(new UserRequestWithFakeDataFixture);
    expect($response->json())->toEqual([
        'name' => 'Sammyjo20',
        'actual_name' => 'Sam',
        'twitter' => '@carre_sam',
    ]);
});

it('can mix requests that implement the HasFakeData interface with inline mocks', function () {
    $connector = TestConnector::make()->withRequestMocks([
        UserRequestWithFakeDataFixture::class,
        UserRequest::class => ['Sam'],
    ]);

    $responseA = $connector->send(new UserRequestWithFakeDataFixture);
    expect($responseA->json())->toEqual([
        'name' => 'Sammyjo20',
        'actual_name' => 'Sam',
        'twitter' => '@carre_sam',
    ]);

    $responseB = $connector->send(new UserRequest);
    expect($responseB->json())->toEqual(['Sam']);
});

it('can mock a request that does not implement the HasFakeData interface', function () {
    $connector = TestConnector::make()->withRequestMocks([
        SoloUserRequest::class,
    ]);

    $response = $connector->send(new SoloUserRequest);
    expect($response->status())->toEqual(200);
    expect($response->json())->toEqual([]);
});
