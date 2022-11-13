<?php declare(strict_types=1);

use Saloon\Repositories\ArrayStore;

test('the store is empty by default', function () {
    $store = new ArrayStore();

    expect($store->all())->toEqual([]);
});

test('you can set it', function () {
    $store = new ArrayStore();

    $store->set(['name' => 'Sam']);

    expect($store->all())->toEqual(['name' => 'Sam']);
});

test('you can add an item', function () {
    $store = new ArrayStore();
    $store->add('name', 'Sam');

    expect($store->all())->toEqual(['name' => 'Sam']);
});

test('you can add an item based on condition', function () {
    $store = new ArrayStore();
    $store->addWhen(true, 'name', 'Gareth');
    $store->addWhen(false, 'name', 'Sam');
    $store->addWhen(true, 'sidekick', fn () => 'Mantas');
    $store->addWhen(false, 'sidekick', fn () => 'Teo');

    expect($store->all())->toEqual(['name' => 'Gareth', 'sidekick' => 'Mantas']);
});

test('you can delete an item', function () {
    $store = new ArrayStore(['name' => 'Sam']);
    $store->remove('name');

    expect($store->all())->toEqual([]);
});

test('you can get an item', function () {
    $store = new ArrayStore(['name' => 'Sam']);

    expect($store->get('name'))->toEqual('Sam');
});

test('you can get all items', function () {
    $store = new ArrayStore(['name' => 'Sam', 'superhero' => 'Iron Man']);

    expect($store->all())->toEqual(['name' => 'Sam', 'superhero' => 'Iron Man']);
});

test('you can merge items together into the content store', function () {
    $store = new ArrayStore(['name' => 'Sam', 'superhero' => 'Iron Man']);

    $store->merge(['sidekick' => 'Gareth'], ['superhero' => 'Black Widow']);

    expect($store->all())->toEqual(['name' => 'Sam', 'sidekick' => 'Gareth', 'superhero' => 'Black Widow']);
});

test('you can check if the store is empty or not', function () {
    $store = new ArrayStore();

    expect($store->isEmpty())->toBeTrue();
    expect($store->isNotEmpty())->toBeFalse();

    $store->add('name', 'Sam');

    expect($store->isEmpty())->toBeFalse();
    expect($store->isNotEmpty())->toBeTrue();
});
