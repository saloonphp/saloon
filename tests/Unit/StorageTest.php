<?php declare(strict_types=1);

use League\Flysystem\Filesystem;
use Saloon\Helpers\Storage;
use League\Flysystem\Local\LocalFilesystemAdapter;
use Saloon\Exceptions\DirectoryNotFoundException;

test('it will throw an exception if the base directory does not exist', function () {
    new Storage('example');
})->throws(DirectoryNotFoundException::class, 'The directory "example" does not exist or is not a valid directory.');

test('you can check if a file exists', function () {
    $storage = new Storage('tests');

    expect($storage->exists('Pest.php'))->toBeTrue();
    expect($storage->missing('Pest.php'))->toBeFalse();
});

test('you can check if a file is missing', function () {
    $storage = new Storage('tests');

    expect($storage->exists('HelloWorld.php'))->toBeFalse();
    expect($storage->missing('HelloWorld.php'))->toBeTrue();
});

test('you can retrieve a file from storage', function () {
    $storage = new Storage('tests');

    $file = $storage->get('Pest.php');

    expect($file)->toEqual(file_get_contents('tests/Pest.php'));
});

test('you can put a file in storage', function () {
    $filesystem = new Filesystem(new LocalFilesystemAdapter('tests/Fixtures/Saloon'));
    $filesystem->deleteDirectory('/');
    $filesystem->createDirectory('/');

    $storage = new Storage('tests/Fixtures/Saloon');

    expect($storage->exists('example.txt'))->toBeFalse();

    $storage->put('example.txt', 'Hello World');

    expect($storage->exists('example.txt'))->toBeTrue();

    expect($storage->get('example.txt'))->toEqual('Hello World');
});

test('it will create a file with nested folders', function () {
    $filesystem = new Filesystem(new LocalFilesystemAdapter('tests/Fixtures/Saloon'));
    $filesystem->deleteDirectory('/');
    $filesystem->createDirectory('/');

    $storage = new Storage('tests/Fixtures/Saloon');

    expect($storage->exists('some/other/directories/example.txt'))->toBeFalse();

    $storage->put('some/other/directories/example.txt', 'Hello World');

    expect($storage->exists('some/other/directories/example.txt'))->toBeTrue();

    expect($storage->get('some/other/directories/example.txt'))->toEqual('Hello World');
});
