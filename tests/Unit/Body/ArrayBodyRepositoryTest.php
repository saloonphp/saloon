<?php

declare(strict_types=1);

use Saloon\Contracts\Body\MergeableBody;
use Saloon\Repositories\Body\ArrayBodyRepository;

test('the store is empty by default', function () {
    $body = new ArrayBodyRepository();

    expect($body->get())->toEqual([]);
});

test('the store can have an array provided', function () {
    $body = new ArrayBodyRepository([
        'name' => 'Sam',
        'sidekick' => 'Mantas',
    ]);

    expect($body->get())->toEqual([
        'name' => 'Sam',
        'sidekick' => 'Mantas',
    ]);
});

test('you can set it', function () {
    $body = new ArrayBodyRepository();

    $body->set(['name' => 'Sam']);

    expect($body->get())->toEqual(['name' => 'Sam']);
});

test('it will throw an exception if you set a non-array', function () {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('The value must be an array');

    $body = new ArrayBodyRepository();
    $body->set('Sam');
});

test('you can add an item', function () {
    $body = new ArrayBodyRepository();

    $body->add('name', 'Sam');

    expect($body->get())->toEqual(['name' => 'Sam']);
});

test('you can add an item with an integer key', function () {
    $body = new ArrayBodyRepository();

    $body->add(1, 'Sam');

    expect($body->get())->toEqual([1 => 'Sam']);
});

test('you can add an item without a key', function () {
    $body = new ArrayBodyRepository();

    $body->add(null, 'Sam');

    expect($body->get())->toEqual(['Sam']);
});

test('you can conditionally add items to the array store', function () {
    $body = new ArrayBodyRepository();

    $body->when(true, fn (ArrayBodyRepository $body) => $body->add('name', 'Gareth'));
    $body->when(false, fn (ArrayBodyRepository $body) => $body->add('name', 'Sam'));
    $body->when(true, fn (ArrayBodyRepository $body) => $body->add('sidekick', 'Mantas'));
    $body->when(false, fn (ArrayBodyRepository $body) => $body->add('sidekick', 'Teo'));

    expect($body->get())->toEqual(['name' => 'Gareth', 'sidekick' => 'Mantas']);
});

test('you can delete an item', function () {
    $body = new ArrayBodyRepository();

    $body->add('name', 'Sam');
    $body->remove('name');

    expect($body->get())->toEqual([]);
});

test('you can delete an item with an integer key', function () {
    $body = new ArrayBodyRepository();

    $body->add(1, 'Sam');
    $body->remove(1);

    expect($body->get())->toEqual([]);
});

test('you can get an item', function () {
    $body = new ArrayBodyRepository();

    $body->add('name', 'Sam');

    expect($body->get('name'))->toEqual('Sam');

    // When omitting the key it should act like `->all()`

    expect($body->get())->toEqual(['name' => 'Sam']);
});

test('you can get an item with an integer key', function () {
    $body = new ArrayBodyRepository();

    $body->add(2, 'Sam');

    expect($body->get(2))->toEqual('Sam');
});

test('you can get all items', function () {
    $body = new ArrayBodyRepository();

    $body->add('name', 'Sam');
    $body->add('superhero', 'Iron Man');

    $allResults = ['name' => 'Sam', 'superhero' => 'Iron Man'];

    expect($body->all())->toEqual($allResults);
    expect($body->get())->toEqual($allResults);
});

test('you can merge items together into the body repository', function () {
    $body = new ArrayBodyRepository();

    expect($body)->toBeInstanceOf(MergeableBody::class);

    $body->add('name', 'Sam');
    $body->add('sidekick', 'Mantas');

    $body->merge(['sidekick' => 'Gareth'], ['superhero' => 'Black Widow']);

    expect($body->get())->toEqual(['name' => 'Sam', 'sidekick' => 'Gareth', 'superhero' => 'Black Widow']);
});

test('you can check if the store is empty or not', function () {
    $body = new ArrayBodyRepository();

    expect($body->isEmpty())->toBeTrue();
    expect($body->isNotEmpty())->toBeFalse();

    $body->add('name', 'Sam');

    expect($body->isEmpty())->toBeFalse();
    expect($body->isNotEmpty())->toBeTrue();
});
