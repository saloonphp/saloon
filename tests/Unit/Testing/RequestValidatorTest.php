<?php

declare(strict_types=1);

namespace Saloon\Tests\Unit;

use Saloon\Helpers\Testing\RequestValidator;
use Saloon\Tests\Fixtures\Requests\HasJsonBodyRequest;

test('you can validate a simple request using the `instance of` check', function () {
    $request = connector()->createPendingRequest(new HasJsonBodyRequest());

    $validator = RequestValidator::for($request)
        ->instanceOf(HasJsonBodyRequest::class);

    expect($validator->validate())->toBeTrue();
});

test('you can get an array of errors', function () {
    $request = connector()->createPendingRequest(new HasJsonBodyRequest());

    $validator = RequestValidator::for($request)
        ->instanceOf(HasJsonBodyRequest::class);

    $expected = [
        'The request is not an instance of Saloon\Tests\Fixtures\Requests\HasJsonBodyRequest',
    ];

    expect($validator->errors())->toBe($expected);
});
