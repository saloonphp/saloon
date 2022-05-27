<?php

use Sammyjo20\Saloon\Helpers\ContentBag;

test('the bag is empty by default', function () {
    $bag = new ContentBag();

    expect($bag->all())->toEqual([]);
});

test('you can set it', function () {
    $bag = new ContentBag();

    $bag->set(['name' => 'Sam']);

    expect($bag->all())->toEqual(['name' => 'Sam']);
});

test('you can add an item', function () {
    $bag = new ContentBag();
    $bag->put('name', 'Sam');

    expect($bag->all())->toEqual(['name' => 'Sam']);
});

test('you can delete an item', function () {
    $bag = new ContentBag(['name' => 'Sam']);
    $bag->delete('name');

    expect($bag->all())->toEqual([]);
});

test('you can get an item', function () {
    $bag = new ContentBag(['name' => 'Sam']);

    expect($bag->get('name'))->toEqual('Sam');
});

test('you can get all items', function () {
    $bag = new ContentBag(['name' => 'Sam', 'superhero' => 'Iron Man']);

    expect($bag->all())->toEqual(['name' => 'Sam', 'superhero' => 'Iron Man']);
});
