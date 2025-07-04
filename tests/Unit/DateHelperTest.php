<?php

declare(strict_types=1);

use Saloon\Helpers\DateHelper;

test('A fixed time can be set in the tests', function () {
    $time = new \DateTimeImmutable('2021-01-01 12:00:00 +00:00');
    DateHelper::setFixedTime($time);

    expect(DateHelper::now())->toEqual($time);

    DateHelper::useSystemTime();

    expect(DateHelper::now())->not->toEqual($time);
});
