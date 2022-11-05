<?php

use Sammyjo20\Saloon\Tests\Fixtures\Requests\UserRequest;

it('accepts an array for requests', function () {
    //
});

it('accepts a generator for requests', function () {
    //

    $requests = function ($total) {
        for ($i = 0; $i < $total; $i++) {
            yield new UserRequest;
        }
    };
});

it('accepts a callback that returns an array for requests', function () {
    //
});

it('accepts a callback that returns a generator for requests', function () {
    //
});

test('throws an exception if an invalid item is passed into the iterator', function () {
    //
});
