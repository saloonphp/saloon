<?php

use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Helpers\MockConfig;
use Sammyjo20\Saloon\Exceptions\FixtureMissingException;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\UserRequest;
use Sammyjo20\Saloon\Exceptions\DirectoryNotFoundException;

test('you can change the default fixture path', function () {
    expect(MockConfig::getFixturePath())->toEqual('tests/Fixtures/Saloon');

    MockConfig::setFixturePath('saloon-requests/responses');

    expect(MockConfig::getFixturePath())->toEqual('saloon-requests/responses');
});

test('if the fixture path is invalid it will throw an exception', function () {
    MockConfig::setFixturePath('saloon-requests/responses');

    new MockClient([
        MockResponse::fixture('example'),
    ]);
})->throws(DirectoryNotFoundException::class, 'The directory "saloon-requests/responses" does not exist or is not a valid directory.');

test('you can throw an exception if the fixture does not exist', function () {
    MockConfig::setFixturePath('tests/Fixtures/Saloon');

    expect(MockConfig::isThrowingOnMissingFixtures())->toBeFalse();

    MockConfig::throwOnMissingFixtures();

    $mockClient = new MockClient([
        MockResponse::fixture('example'),
    ]);

    UserRequest::make()->send($mockClient);
})->throws(FixtureMissingException::class, 'The fixture "example.json" could not be found in storage.');
