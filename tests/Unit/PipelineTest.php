<?php

use Sammyjo20\Saloon\Helpers\Pipeline;

test('a pipeline can be executed', function () {
    $pipeline = new Pipeline();
    $number = 0;

    $pipeline
        ->pipe(function ($number) {
            return $number + 5;
        })
        ->pipe(function ($number) {
            return $number * 2;
        })
        ->pipe(function ($number) {
            return $number - 3;
        });

    expect($pipeline->getPipes())->toHaveCount(3);

    $number = $pipeline->process($number);

    expect($number)->toEqual(7);
});

test('a high priority pipe can be added to a pipeline', function () {
    $pipeline = new Pipeline();

    // Since 'Michael' is high priority, it will be executed first.

    $pipeline
        ->pipe(function ($name) {
            return 'Mantas';
        })
        ->pipe(function ($name) {
            return 'Teo';
        })
        ->pipe(function ($name) {
            return 'Michael';
        }, true);

    expect($pipeline->getPipes())->toHaveCount(3);

    $name = $pipeline->process('Sam');

    expect($name)->toEqual('Teo');
});
