<?php

declare(strict_types=1);

use Saloon\Data\MultipartValue;
use Saloon\Contracts\Body\MergeableBody;
use Saloon\Repositories\Body\MultipartBodyRepository;

test('the store is empty by default', function () {
    $body = new MultipartBodyRepository();

    expect($body->get())->toEqual([]);
});

test('the store can have an array of multipart values provided', function () {
    $body = new MultipartBodyRepository([
        new MultipartValue('name', 'Sam'),
        new MultipartValue('sidekick', 'Mantas'),
    ]);

    expect($body->get())->toEqual([
        'name' => new MultipartValue('name', 'Sam'),
        'sidekick' => new MultipartValue('sidekick', 'Mantas'),
    ]);
});

test('the store will throw an exception if set value is not an array', function () {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('The value must be an array');

    $body = new MultipartBodyRepository();
    $body->set('123');
});

test('the store will throw an exception if the array does not contain multipart values', function () {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('The value array must only contain Saloon\Data\MultipartValue objects');

    new MultipartBodyRepository([
        'name' => 'Sam',
        'sidekick' => new MultipartValue('username', 'Sammyjo20'),
    ]);
});

test('you can set it', function () {
    $body = new MultipartBodyRepository();

    $body->set([
        new MultipartValue('username', 'Sammyjo20'),
    ]);

    expect($body->get())->toEqual([
        'username' => new MultipartValue('username', 'Sammyjo20'),
    ]);
});

test('you can add an item', function () {
    $body = new MultipartBodyRepository();

    $body->add('name', 'Sam', 'welcome.txt', ['a' => 'b']);

    expect($body->get())->toEqual([
        'name' => new MultipartValue('name', 'Sam', 'welcome.txt', ['a' => 'b']),
    ]);

    // Test it being overwritten

    $body->add('name', 'Charlotte', 'welcome.txt', ['a' => 'b']);

    expect($body->get())->toEqual([
        'name' => new MultipartValue('name', 'Charlotte', 'welcome.txt', ['a' => 'b']),
    ]);
});

test('you can conditionally add items to the array store', function () {
    $body = new MultipartBodyRepository();

    $body->when(true, fn (MultipartBodyRepository $body) => $body->add('name', 'Gareth'));
    $body->when(false, fn (MultipartBodyRepository $body) => $body->add('name', 'Sam'));
    $body->when(true, fn (MultipartBodyRepository $body) => $body->add('sidekick', 'Mantas'));
    $body->when(false, fn (MultipartBodyRepository $body) => $body->add('sidekick', 'Teo'));

    expect($body->get())->toEqual([
        'name' => new MultipartValue('name', 'Gareth'),
        'sidekick' => new MultipartValue('sidekick', 'Mantas'),
    ]);
});

test('you can delete an item', function () {
    $body = new MultipartBodyRepository();

    $body->add('name', 'Sam');
    $body->remove('name');

    expect($body->get())->toEqual([]);
});

test('you can get an item', function () {
    $body = new MultipartBodyRepository();

    $body->add('name', 'Sam');

    expect($body->get('name'))->toEqual(new MultipartValue('name', 'Sam'));
});

test('you can get all items', function () {
    $body = new MultipartBodyRepository();

    $body->add('name', 'Sam');
    $body->add('superhero', 'Iron Man');

    $allResults = [
        'name' => new MultipartValue('name', 'Sam'),
        'superhero' => new MultipartValue('superhero', 'Iron Man'),
    ];

    expect($body->all())->toEqual($allResults);
    expect($body->get())->toEqual($allResults);
});

test('you can merge items together into the body repository', function () {
    $body = new MultipartBodyRepository();

    expect($body)->toBeInstanceOf(MergeableBody::class);

    $body->add('name', 'Sam');
    $body->add('sidekick', 'Mantas');

    $body->merge([new MultipartValue('sidekick', 'Gareth')], [new MultipartValue('superhero', 'Black Widow')]);

    expect($body->get())->toEqual([
        'name' => new MultipartValue('name', 'Sam'),
        'sidekick' => new MultipartValue('sidekick', 'Gareth'),
        'superhero' => new MultipartValue('superhero', 'Black Widow'),
    ]);
});

test('it will throw an exception if the merged items are not MultipartValue objects', function () {
    $body = new MultipartBodyRepository();

    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('The value array must only contain Saloon\Data\MultipartValue objects');

    $body->merge([new MultipartValue('sidekick', 'Gareth')], ['superhero' => 'Black Widow']);
});

test('you can check if the store is empty or not', function () {
    $body = new MultipartBodyRepository();

    expect($body->isEmpty())->toBeTrue();
    expect($body->isNotEmpty())->toBeFalse();

    $body->add('name', 'Sam');

    expect($body->isEmpty())->toBeFalse();
    expect($body->isNotEmpty())->toBeTrue();
});
