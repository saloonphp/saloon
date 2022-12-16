<?php

declare(strict_types=1);

use Saloon\Repositories\Body\StringBodyRepository;

test('the store is empty by default', function () {
    $body = new StringBodyRepository();

    expect($body->all())->toBeNull();
});


test('the store can have a default string provided', function () {
    $body = new StringBodyRepository('Yeehaw!');

    expect($body->all())->toEqual('Yeehaw!');
});

test('you can set it', function () {
    $body = new StringBodyRepository('Sam');

    $body->set('Yeehaw!');

    expect($body->all())->toEqual('Yeehaw!');
});

test('you can conditionally set on the store', function () {
    $body = new StringBodyRepository();

    $body->when(true, fn (StringBodyRepository $body) => $body->set('Gareth'));
    $body->when(false, fn (StringBodyRepository $body) => $body->set('Sam'));

    expect($body->all())->toEqual('Gareth');
});

test('you can check if the store is empty or not', function () {
    $body = new StringBodyRepository();

    expect($body->isEmpty())->toBeTrue();
    expect($body->isNotEmpty())->toBeFalse();

    $body->set('Sam');

    expect($body->isEmpty())->toBeFalse();
    expect($body->isNotEmpty())->toBeTrue();
});
