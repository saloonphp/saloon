<?php

declare(strict_types=1);

namespace Saloon\Tests\Unit\Testing\Checks;

use Saloon\Tests\Fixtures\Requests\HasJsonBodyRequest;
use Saloon\Helpers\Testing\Checks\EndpointEndsWithCheck;

test('you can validate if a given request ends with the expected url', function () {
    $actual = connector()->createPendingRequest(new HasJsonBodyRequest());

    $check = EndpointEndsWithCheck::make(
        $actual,
        '/user'
    );

    expect($check->valid())->toBeTrue();
});

test('you can validate if a given request does not end with the expected url', function () {
    $actual = connector()->createPendingRequest(new HasJsonBodyRequest());

    $check = EndpointEndsWithCheck::make(
        $actual,
        '/users',
    );

    expect($check->valid())->toBeFalse();
    expect($check->message())->toEqual('The url did not end with \'/users\'');
});
