<?php

declare(strict_types=1);

namespace Saloon\Tests\Unit\Testing\Checks;

use Saloon\Http\PendingRequest;
use Saloon\Helpers\Testing\Checks\BodyEqualsCheck;
use Saloon\Tests\Fixtures\Requests\HasJsonBodyRequest;

test('you can validate body using a closure returning true', function () {
    $actual = connector()->createPendingRequest(new HasJsonBodyRequest());

    $check = BodyEqualsCheck::make(
        $actual,
        fn (PendingRequest $request) => true
    );

    expect($check->valid())->toBeTrue();
});

test('you can validate body using a closure returning false', function () {
    $actual = connector()->createPendingRequest(new HasJsonBodyRequest());

    $check = BodyEqualsCheck::make(
        $actual,
        fn (PendingRequest $request) => false
    );

    expect($check->valid())->toBeFalse();
});

test('you can validate body using path and expected value', function () {
    $actual = connector()->createPendingRequest(new HasJsonBodyRequest());

    $check = BodyEqualsCheck::make(
        $actual,
        'name',
        'Sam'
    );

    expect($check->valid())->toBeTrue();
});

test('you can validate body using nested path and expected value', function () {
    $request = new HasJsonBodyRequest();

    $request->body()->add('organisation', [
        'name' => 'Space Cowboy',
    ]);

    $actual = connector()->createPendingRequest($request);

    $check = BodyEqualsCheck::make(
        $actual,
        'organisation.name',
        'Space Cowboy'
    );

    expect($check->valid())->toBeTrue();
});

test('check will fail when path does not exist', function () {
    $request = new HasJsonBodyRequest();

    $actual = connector()->createPendingRequest($request);

    $check = BodyEqualsCheck::make(
        $actual,
        'organisation.name',
        'Space Cowboy'
    );

    expect($check->valid())->toBeFalse();
    expect($check->message())->toEqual('The body did not contain the expected value');
});

test('check will fail when path is not equal to expected value', function () {
    $request = new HasJsonBodyRequest();

    $request->body()->add('organisation', [
        'name' => 'Space Cowboy',
    ]);

    $actual = connector()->createPendingRequest($request);

    $check = BodyEqualsCheck::make(
        $actual,
        'organisation.name',
        'Saloon'
    );

    expect($check->valid())->toBeFalse();
    expect($check->message())->toEqual('The body did not contain the expected value');
});
