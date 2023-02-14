<?php

declare(strict_types=1);


use Saloon\Repositories\Body\IntegerBodyRepository;

test('the store is empty by default', function () {
    $body = new IntegerBodyRepository();

    expect($body->all())->toEqual(null);
});

test('the store can have an array provided', function () {
    $body = new IntegerBodyRepository(1);

    expect($body->all())->toEqual(1);
});

test('you can set it', function () {
    $body = new IntegerBodyRepository();

    $body->set(1);

    expect($body->all())->toEqual(1);
});

test('it will throw an exception if you set a non-integer', function () {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('The value must be an integer');

    $body = new IntegerBodyRepository();
    $body->set([]);
});


test('you can assert a value is greater than', function () {
    $body = new IntegerBodyRepository();
    $body->set(1);

    expect($body->greaterThan(2))->toBeFalse();
    expect($body->greaterThan(0))->toBeTrue();
    expect($body->greaterThan(1))->toBeFalse();
    expect($body->greaterOrEqualThan(1))->toBeTrue();
});

test('you can assert a value is lesser than', function () {
    $body = new IntegerBodyRepository();
    $body->set(1);

    expect($body->lesserThan(2))->toBeTrue();
    expect($body->lesserThan(0))->toBeFalse();
    expect($body->lesserThan(1))->toBeFalse();
    expect($body->lesserOrEqualThan(1))->toBeTrue();
});

test('you can delete an item with an integer key', function () {
    $body = new IntegerBodyRepository();
    $body->remove(1);

    expect($body->all())->toEqual([]);
});

test('you can get an item', function () {
    $body = new ArrayBodyRepository();

    $body->add('name', 'Sam');

    expect($body->get('name'))->toEqual('Sam');
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
