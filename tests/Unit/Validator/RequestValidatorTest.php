<?php

declare(strict_types=1);

namespace Saloon\Tests\Unit;

use Saloon\Helpers\Validators\RequestValidator;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Requests\HasJsonBodyRequest;

test('you can validate a simple request using the `instance of` check', function () {
    $validator = RequestValidator::for(new HasJsonBodyRequest())
        ->instanceOf(HasJsonBodyRequest::class);

    expect($validator->validate())->toBeTrue();
});

test('you can get an array of errors', function () {
    $validator = RequestValidator::for(new HasJsonBodyRequest())
        ->instanceOf(HasJsonBodyRequest::class);

    $expected = [
        'The request is not an instance of Saloon\Tests\Fixtures\Requests\HasJsonBodyRequest',
    ];

    expect($validator->errors())->toBe($expected);
});

test('you can make a validator instance for a pending request', function () {
    $request = new UserRequest;
    $pendingRequest = connector()->createPendingRequest($request);

    $validator = RequestValidator::forPendingRequest($pendingRequest)
        ->instanceOf(UserRequest::class);

    expect($validator->validate())->toBeTrue();
});
