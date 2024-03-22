<?php

declare(strict_types=1);

namespace Saloon\Tests\Unit\Testing\Checks;

use Saloon\Tests\Fixtures\Requests\HasJsonBodyRequest;
use Saloon\Helpers\Testing\Checks\EndpointStartsWithCheck;

test('you can validate if a given request start with the expected url', function () {
    $actual = connector()->createPendingRequest(new HasJsonBodyRequest());

    $check = EndpointStartsWithCheck::make(
        $actual,
        '/api'
    );

    expect($check->valid())->toBeTrue();
});

test('you can validate if a given request does not start with the expected url', function () {
    $actual = connector()->createPendingRequest(new HasJsonBodyRequest());

    $check = EndpointStartsWithCheck::make(
        $actual,
        '/apis',
    );

    expect($check->valid())->toBeFalse();
    expect($check->message())->toEqual('The url did not start with \'/apis\'');
});
