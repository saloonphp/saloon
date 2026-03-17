<?php

declare(strict_types=1);

use Saloon\Helpers\Storage;
use Saloon\Http\Faking\Fixture;
use Saloon\Data\RecordedResponse;
use Saloon\Exceptions\FixtureException;

beforeEach(function () {
    $this->fixtureBaseDir = sys_get_temp_dir() . '/saloon_fixture_traversal_' . uniqid('', true);
    mkdir($this->fixtureBaseDir, 0777, true);
});

afterEach(function () {
    if (isset($this->fixtureBaseDir) && is_dir($this->fixtureBaseDir)) {
        array_map('unlink', glob($this->fixtureBaseDir . '/*') ?: []);
        rmdir($this->fixtureBaseDir);
    }
    $escapeWritePath = sys_get_temp_dir() . '/traversal_write_test.json';
    if (file_exists($escapeWritePath)) {
        unlink($escapeWritePath);
    }
    $escapeReadPath = sys_get_temp_dir() . '/traversal_read_target.json';
    if (file_exists($escapeReadPath)) {
        unlink($escapeReadPath);
    }
});

test('fixture name with path traversal throws when getting mock response and does not read outside base', function () {
    $storage = new Storage($this->fixtureBaseDir, true);

    $externalPath = sys_get_temp_dir() . '/traversal_read_target.json';
    $secretContent = 'read_from_outside';
    file_put_contents($externalPath, json_encode([
        'statusCode' => 200,
        'headers' => [],
        'data' => '{"secret":"' . $secretContent . '"}',
        'context' => [],
    ]));

    $traversalName = '..' . DIRECTORY_SEPARATOR . 'traversal_read_target';
    $fixture = new Fixture($traversalName, $storage);

    expect(fn () => $fixture->getMockResponse())
        ->toThrow(FixtureException::class, 'The fixture name must not contain directory traversal components or invalid characters. Only alphanumeric characters, hyphens, slashes, and underscores are allowed.');

    expect(file_get_contents($externalPath))->toContain($secretContent);
});

test('fixture name with path traversal throws when storing and does not write outside base', function () {
    $storage = new Storage($this->fixtureBaseDir, true);

    $traversalName = '..' . DIRECTORY_SEPARATOR . 'traversal_write_test';
    $fixture = new Fixture($traversalName, $storage);

    $recordedResponse = new RecordedResponse(200, [], '{"pwned":true}');

    expect(fn () => $fixture->store($recordedResponse))
        ->toThrow(FixtureException::class, 'The fixture name must not contain directory traversal components or invalid characters. Only alphanumeric characters, hyphens, slashes, and underscores are allowed.');

    $escapePath = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'traversal_write_test.json';
    expect(file_exists($escapePath))->toBeFalse();
});
