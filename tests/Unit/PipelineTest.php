<?php

declare(strict_types=1);

use Saloon\Helpers\Pipeline;

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
