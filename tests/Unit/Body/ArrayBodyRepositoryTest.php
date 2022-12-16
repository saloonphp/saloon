<?php

declare(strict_types=1);

use Saloon\Repositories\Body\ArrayBodyRepository;

test('the store is empty by default', function () {
    $body = new ArrayBodyRepository();

    expect($body->all())->toEqual([]);
});

test('the store can have an array provided', function () {
    $body = new ArrayBodyRepository([
        'name' => 'Sam',
        'sidekick' => 'Mantas',
    ]);

    expect($body->all())->toEqual([
        'name' => 'Sam',
        'sidekick' => 'Mantas',
    ]);
});

test('you can set it', function () {
    $body = new ArrayBodyRepository();

    $body->set(['name' => 'Sam']);

    expect($body->all())->toEqual(['name' => 'Sam']);
});

test('you can add an item', function () {
    $body = new ArrayBodyRepository();

    $body->add('name', 'Sam');

    expect($body->all())->toEqual(['name' => 'Sam']);
});

test('you can conditionally add items to the array store', function () {
    $body = new ArrayBodyRepository();

    $body->when(true, fn (ArrayBodyRepository $body) => $body->add('name', 'Gareth'));
    $body->when(false, fn (ArrayBodyRepository $body) => $body->add('name', 'Sam'));
    $body->when(true, fn (ArrayBodyRepository $body) => $body->add('sidekick', 'Mantas'));
    $body->when(false, fn (ArrayBodyRepository $body) => $body->add('sidekick', 'Teo'));

    expect($body->all())->toEqual(['name' => 'Gareth', 'sidekick' => 'Mantas']);
});

test('you can delete an item', function () {
    $body = new ArrayBodyRepository();

    $body->add('name', 'Sam');
    $body->remove('name');

    expect($body->all())->toEqual([]);
});

test('you can get an item', function () {
    $body = new ArrayBodyRepository();

    $body->add('name', 'Sam');

    expect($body->get('name'))->toEqual('Sam');
});

test('you can get all items', function () {
    $body = new ArrayBodyRepository();

    $body->add('name', 'Sam');
    $body->add('superhero', 'Iron Man');

    expect($body->all())->toEqual(['name' => 'Sam', 'superhero' => 'Iron Man']);
});

test('you can merge items together into the body repository', function () {
    $body = new ArrayBodyRepository();

    $body->add('name', 'Sam');
    $body->add('sidekick', 'Mantas');

    $body->merge(['sidekick' => 'Gareth'], ['superhero' => 'Black Widow']);

    expect($body->all())->toEqual(['name' => 'Sam', 'sidekick' => 'Gareth', 'superhero' => 'Black Widow']);
});

test('you can check if the store is empty or not', function () {
    $body = new ArrayBodyRepository();

    expect($body->isEmpty())->toBeTrue();
    expect($body->isNotEmpty())->toBeFalse();

    $body->add('name', 'Sam');

    expect($body->isEmpty())->toBeFalse();
    expect($body->isNotEmpty())->toBeTrue();
});
