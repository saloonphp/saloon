<?php

declare(strict_types=1);

namespace Saloon\Tests\Unit\Validator\Checks;

use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Helpers\Testing\Checks\InstanceOfCheck;
use Saloon\Tests\Fixtures\Requests\HasJsonBodyRequest;

test('you can validate if a given request matches the expected instance', function () {
    $actual = connector()->createPendingRequest(new HasJsonBodyRequest());

    $check = InstanceOfCheck::make(
        $actual,
        HasJsonBodyRequest::class
    );

    expect($check->valid())->toBeTrue();
});

test('you can validate if a given request does not match the expected instance', function () {
    $actual = connector()->createPendingRequest(new HasJsonBodyRequest());

    $check = InstanceOfCheck::make(
        $actual,
        UserRequest::class
    );

    expect($check->valid())->toBeFalse();
    expect($check->message())->toEqual('The request is not an instance of Saloon\Tests\Fixtures\Requests\UserRequest');
});
