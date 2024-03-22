<?php

declare(strict_types=1);

namespace Saloon\Tests\Unit\Testing\Checks;

use Saloon\Tests\Fixtures\Requests\HasJsonBodyRequest;
use Saloon\Helpers\Testing\Checks\EndpointContainsCheck;

test('you can validate if a given request contains the expected url', function () {
    $actual = connector()->createPendingRequest(new HasJsonBodyRequest());

    $check = EndpointContainsCheck::make(
        $actual,
        'api/us'
    );

    expect($check->valid())->toBeTrue();
});

test('you can validate if a given request contains the given closure', function () {
    $actual = connector()->createPendingRequest(new HasJsonBodyRequest());

    $check = EndpointContainsCheck::make(
        $actual,
        fn (string $path) => str_contains($path, 'api/us')
    );

    expect($check->valid())->toBeTrue();
});


test('you can validate if a given request does not start with the expected url', function () {
    $actual = connector()->createPendingRequest(new HasJsonBodyRequest());

    $check = EndpointContainsCheck::make(
        $actual,
        '/apis/us',
    );

    expect($check->valid())->toBeFalse();
    expect($check->message())->toEqual('The url did not contain \'/apis/us\'');
});
