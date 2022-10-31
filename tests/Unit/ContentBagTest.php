<?php

use Sammyjo20\Saloon\Repositories\ArrayRepository;

test('the bag is empty by default', function () {
    $bag = new ArrayRepository();

    expect($bag->all())->toEqual([]);
});

test('you can set it', function () {
    $bag = new ArrayRepository();

    $bag->set(['name' => 'Sam']);

    expect($bag->all())->toEqual(['name' => 'Sam']);
});

test('you can add an item', function () {
    $bag = new ArrayRepository();
    $bag->add('name', 'Sam');

    expect($bag->all())->toEqual(['name' => 'Sam']);
});

test('you can add an item based on condition', function () {
    $bag = new ArrayRepository();
    $bag->addWhen(true, 'name', 'Gareth');
    $bag->addWhen(false, 'name', 'Sam');
    $bag->addWhen(true, 'sidekick', fn () => 'Mantas');
    $bag->addWhen(false, 'sidekick', fn () => 'Teo');


    expect($bag->all())->toEqual(['name' => 'Gareth', 'sidekick' => 'Mantas']);
});

test('you can delete an item', function () {
    $bag = new ArrayRepository(['name' => 'Sam']);
    $bag->remove('name');

    expect($bag->all())->toEqual([]);
});

test('you can get an item', function () {
    $bag = new ArrayRepository(['name' => 'Sam']);

    expect($bag->get('name'))->toEqual('Sam');
});

test('you can get all items', function () {
    $bag = new ArrayRepository(['name' => 'Sam', 'superhero' => 'Iron Man']);

    expect($bag->all())->toEqual(['name' => 'Sam', 'superhero' => 'Iron Man']);
});

test('you can merge items together into the content bag', function () {
    $bag = new ArrayRepository(['name' => 'Sam', 'superhero' => 'Iron Man']);

    $bag->merge(['sidekick' => 'Gareth'], ['superhero' => 'Black Widow']);

    expect($bag->all())->toEqual(['name' => 'Sam', 'sidekick' => 'Gareth', 'superhero' => 'Black Widow']);
});
