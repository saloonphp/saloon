<?php declare(strict_types=1);

use Sammyjo20\Saloon\Repositories\ArrayStore;

test('the bag is empty by default', function () {
    $bag = new ArrayStore();

    expect($bag->all())->toEqual([]);
});

test('you can set it', function () {
    $bag = new ArrayStore();

    $bag->set(['name' => 'Sam']);

    expect($bag->all())->toEqual(['name' => 'Sam']);
});

test('you can add an item', function () {
    $bag = new ArrayStore();
    $bag->add('name', 'Sam');

    expect($bag->all())->toEqual(['name' => 'Sam']);
});

test('you can add an item based on condition', function () {
    $bag = new ArrayStore();
    $bag->addWhen(true, 'name', 'Gareth');
    $bag->addWhen(false, 'name', 'Sam');
    $bag->addWhen(true, 'sidekick', fn () => 'Mantas');
    $bag->addWhen(false, 'sidekick', fn () => 'Teo');


    expect($bag->all())->toEqual(['name' => 'Gareth', 'sidekick' => 'Mantas']);
});

test('you can delete an item', function () {
    $bag = new ArrayStore(['name' => 'Sam']);
    $bag->remove('name');

    expect($bag->all())->toEqual([]);
});

test('you can get an item', function () {
    $bag = new ArrayStore(['name' => 'Sam']);

    expect($bag->get('name'))->toEqual('Sam');
});

test('you can get all items', function () {
    $bag = new ArrayStore(['name' => 'Sam', 'superhero' => 'Iron Man']);

    expect($bag->all())->toEqual(['name' => 'Sam', 'superhero' => 'Iron Man']);
});

test('you can merge items together into the content bag', function () {
    $bag = new ArrayStore(['name' => 'Sam', 'superhero' => 'Iron Man']);

    $bag->merge(['sidekick' => 'Gareth'], ['superhero' => 'Black Widow']);

    expect($bag->all())->toEqual(['name' => 'Sam', 'sidekick' => 'Gareth', 'superhero' => 'Black Widow']);
});
