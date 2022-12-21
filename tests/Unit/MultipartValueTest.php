<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Utils;
use Saloon\Data\MultipartValue;
use Saloon\Tests\Fixtures\Requests\UserRequest;

test('it can accept different values', function (mixed $value) {
    $multipartValue = new MultipartValue('test', $value);

    expect($multipartValue->value)->toEqual($value);
})->with([
    fn () => Utils::streamFor('hello'),
    fn () => fopen(sprintf('data://text/plain,%s', 'hello'), 'rb'),
    fn () => 'hello',
    fn () => 123,
    fn () => 123.50,
]);

test('it will throw an exception on invalid values', function (mixed $value) {
    $this->expectException(InvalidArgumentException::class);
    $this->expectExceptionMessage('The value property must be either a Psr\Http\Message\StreamInterface, resource, string or numeric.');

    new MultipartValue('test', $value);
})->with([
    fn () => [],
    fn () => new UserRequest,
]);
