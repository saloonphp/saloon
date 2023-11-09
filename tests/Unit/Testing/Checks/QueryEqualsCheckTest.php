<?php

declare(strict_types=1);

namespace Saloon\Tests\Unit\Testing\Checks;

use Saloon\Repositories\ArrayStore;
use Saloon\Helpers\Testing\Checks\QueryEqualsCheck;
use Saloon\Tests\Fixtures\Requests\HasJsonBodyRequest;
use Saloon\Tests\Fixtures\Requests\QueryParameterRequest;

test('you can validate query using a closure returning true', function () {
    $actual = connector()->createPendingRequest(new HasJsonBodyRequest());

    $check = QueryEqualsCheck::make(
        $actual,
        fn (ArrayStore $request) => true
    );

    expect($check->valid())->toBeTrue();
});

test('you can validate query using a closure returning false', function () {
    $actual = connector()->createPendingRequest(new HasJsonBodyRequest());

    $check = QueryEqualsCheck::make(
        $actual,
        fn (ArrayStore $request) => false
    );

    expect($check->valid())->toBeFalse();
});

test('you can validate query using path and expected value', function () {
    $actual = connector()->createPendingRequest(new QueryParameterRequest());

    $check = QueryEqualsCheck::make(
        $actual,
        'per_page',
        100
    );

    expect($check->valid())->toBeTrue();
});

test('you can validate query using nested path and expected value', function () {
    $request = new QueryParameterRequest();

    $request->query()->add('organisation', [
        'name' => 'Space Cowboy',
    ]);

    $actual = connector()->createPendingRequest($request);

    $check = QueryEqualsCheck::make(
        $actual,
        'organisation.name',
        'Space Cowboy'
    );

    expect($check->valid())->toBeTrue();
});

test('check will fail when path does not exist', function () {
    $request = new QueryParameterRequest();

    $actual = connector()->createPendingRequest($request);

    $check = QueryEqualsCheck::make(
        $actual,
        'organisation',
        'Space Cowboy'
    );

    expect($check->valid())->toBeFalse();
    expect($check->message())->toEqual('The query parameters did not contain the expected value');
});

test('check will fail when path is not equal to expected value', function () {
    $request = new QueryParameterRequest();

    $request->query()->add('organisation', 'Space Cowboy');

    $actual = connector()->createPendingRequest($request);

    $check = QueryEqualsCheck::make(
        $actual,
        'organisation',
        'Saloon'
    );

    expect($check->valid())->toBeFalse();
    expect($check->message())->toEqual('The query parameters did not contain the expected value');
});
