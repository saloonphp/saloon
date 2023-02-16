<?php

declare(strict_types=1);


use Saloon\Repositories\IntegerStore;

test('the store is empty by default', function () {
    $store = new IntegerStore();

    expect($store->get())->toEqual(null);
});

test('the store can have an array provided', function () {
    $store = new IntegerStore(1);

    expect($store->get())->toEqual(1);
});

test('you can set it', function () {
    $store = new IntegerStore();

    $store->set(1);

    expect($store->get())->toEqual(1);
});

test('you can check if the store is empty', function () {
    $store = new IntegerStore();

    expect($store->isEmpty())->toBeTrue();
    expect($store->isNotEmpty())->toBeFalse();

    $store->set(0);

    expect($store->isEmpty())->toBeTrue();
    expect($store->isNotEmpty())->toBeFalse();

    $store->set(1);

    expect($store->isEmpty())->toBeFalse();
    expect($store->isNotEmpty())->toBeTrue();
});
