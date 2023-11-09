<?php

declare(strict_types=1);

namespace Saloon\Tests\Unit;

use Illuminate\Support\Arr;
use Saloon\Repositories\ArrayStore;
use Saloon\Helpers\Testing\RequestValidator;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Requests\HasJsonBodyRequest;
use Saloon\Tests\Fixtures\Requests\QueryParameterRequest;

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

test('you can validate a request using the `body equals` check using path', function () {
    $request = connector()->createPendingRequest(new HasJsonBodyRequest());

    $validator = RequestValidator::for($request)
        ->bodyEquals('name', 'Sam');

    expect($validator->validate())->toBeTrue();
});

test('you can validate a request using the `body equals` check using closure', function () {
    $request = connector()->createPendingRequest(new HasJsonBodyRequest());

    $validator = RequestValidator::for($request)
        ->bodyEquals(fn (array $body) => Arr::get($body, 'name') === 'Sam');

    expect($validator->validate())->toBeTrue();
});

test('you can validate a request using the `query equals` check using path', function () {
    $request = connector()->createPendingRequest(new QueryParameterRequest());

    $validator = RequestValidator::for($request)
        ->queryEquals('per_page', 100);

    expect($validator->validate())->toBeTrue();
});

test('you can validate a request using the `query equals` check using closure', function () {
    $request = connector()->createPendingRequest(new QueryParameterRequest());

    $validator = RequestValidator::for($request)
        ->queryEquals(fn (ArrayStore $query) => $query->get('per_page') === 100);

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
