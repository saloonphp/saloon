<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Utils;
use Saloon\Repositories\Body\StreamBodyRepository;

test('the store is empty by default', function () {
    $body = new StreamBodyRepository();

    expect($body->all())->toBeNull();
});


test('the store can have a default stream provided', function () {
    $temp = tmpfile();

    $body = new StreamBodyRepository($temp);

    expect($body->all())->toEqual(Utils::streamFor($temp));
});

test('you can set it', function () {
    $tempA = fopen('php://memory', 'rw+');
    fwrite($tempA, 'Howdy');

    $tempB = fopen('php://memory', 'rw+');
    fwrite($tempB, 'Yeehaw');

    $body = new StreamBodyRepository($tempA);

    $body->set($tempB);

    expect($body->all())->toEqual(Utils::streamFor($tempB));
});

test('you can conditionally set on the store', function () {
    $body = new StreamBodyRepository();

    $tempA = fopen('php://memory', 'rw+');
    fwrite($tempA, 'Howdy');

    $tempB = fopen('php://memory', 'rw+');
    fwrite($tempB, 'Yeehaw');

    $body->when(true, fn (StreamBodyRepository $body) => $body->set($tempA));
    $body->when(false, fn (StreamBodyRepository $body) => $body->set($tempB));

    expect($body->all())->toEqual(Utils::streamFor($tempA));
});

test('you can check if the store is empty or not', function () {
    $body = new StreamBodyRepository();

    expect($body->isEmpty())->toBeTrue();
    expect($body->isNotEmpty())->toBeFalse();

    $body->set(tmpfile());

    expect($body->isEmpty())->toBeFalse();
    expect($body->isNotEmpty())->toBeTrue();
});

test('it will throw an exception if the value is not a resource or StreamInterface when instantiating', function (mixed $value) {
    $this->expectException(InvalidArgumentException::class);

    new StreamBodyRepository($value);
})->with([
    fn () => 'Howdy',
    fn () => 123,
    fn () => [],
    fn () => false,
]);

test('it will throw an exception if the value is not a resource or StreamInterface when setting', function (mixed $value) {
    $this->expectException(InvalidArgumentException::class);

    new StreamBodyRepository($value);
})->with([
    fn () => 'Howdy',
    fn () => 123,
    fn () => [],
    fn () => false,
]);

test('it allows null values', function () {
    $body = new StreamBodyRepository(null);

    expect($body->all())->toBeNull();
    expect($body->isEmpty())->toBeTrue();

    $body->set(null);

    expect($body->all())->toBeNull();
    expect($body->isEmpty())->toBeTrue();
});
