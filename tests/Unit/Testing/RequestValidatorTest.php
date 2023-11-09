<?php

declare(strict_types=1);

namespace Saloon\Tests\Unit;

use Saloon\Helpers\Testing\RequestValidator;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Requests\HasJsonBodyRequest;

test('you can validate a request using the `instance of` check', function () {
    $request = connector()->createPendingRequest(new HasJsonBodyRequest());

    $validator = RequestValidator::for($request)
        ->instanceOf(HasJsonBodyRequest::class);

    expect($validator->validate())->toBeTrue();
});

test('you can validate a request using the `endpoints ends with` check', function () {
    $request = connector()->createPendingRequest(new HasJsonBodyRequest());

    $validator = RequestValidator::for($request)
        ->endpointEndsWith('/user');

    expect($validator->validate())->toBeTrue();
});

test('you can validate a request using the `body equals` check', function () {
    $request = connector()->createPendingRequest(new HasJsonBodyRequest());

    $validator = RequestValidator::for($request)
        ->bodyEquals('name', 'Sam');

    expect($validator->validate())->toBeTrue();
});

test('you can get an array of errors', function () {
    $request = connector()->createPendingRequest(new HasJsonBodyRequest());

    $validator = RequestValidator::for($request)
        ->instanceOf(UserRequest::class)
        ->endpointEndsWith('/api');

    $expected = [
        'The request is not an instance of Saloon\Tests\Fixtures\Requests\UserRequest',
        'The url did not end with \'/api\'',
    ];

    expect($validator->errors())->toBe($expected);
});

test('errors is empty when validation passes', function () {
    $request = connector()->createPendingRequest(new HasJsonBodyRequest());

    $validator = RequestValidator::for($request)
        ->instanceOf(HasJsonBodyRequest::class)
        ->endpointEndsWith('/user');

    expect($validator->validate())->toBeTrue();
    expect($validator->errors())->toBeEmpty();
});
