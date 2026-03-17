<?php

declare(strict_types=1);

use Saloon\Tests\Fixtures\Requests\UserRequest;

test('you can use the when method to invoke a callback when a given condition is truthy', function () {
    $request = new UserRequest;

    $request->when(true, function (UserRequest $request) {
        $request->headers()->add('X-Name', 'Sam');
    });

    $request->when(false, function (UserRequest $request) {
        $request->headers()->add('X-Name', 'Alex');
    });

    expect($request->headers()->all())->toHaveKey('X-Name', 'Sam');
    expect($request->headers()->all())->not->toHaveKey('X-Name', 'Alex');
});

test('you can use the unless method to invoke a callback when a given condition is falsy', function () {
    $request = new UserRequest;

    $request->unless(true, function (UserRequest $request) {
        $request->headers()->add('X-Name', 'Sam');
    });

    $request->unless(false, function (UserRequest $request) {
        $request->headers()->add('X-Name', 'Alex');
    });

    expect($request->headers()->all())->toHaveKey('X-Name', 'Alex');
    expect($request->headers()->all())->not->toHaveKey('X-Name', 'Sam');
});

test('you can provide a callback as the value of the when condition', function () {
    $request = new UserRequest;

    $request->when(
        fn () => true,
        function (UserRequest $request) {
            $request->headers()->add('X-Name', 'Sam');
        }
    );

    expect($request->headers()->all())->toHaveKey('X-Name', 'Sam');
    expect($request->headers()->all())->not->toHaveKey('X-Name', 'Alex');
});

test('you can provide a callback as the value of the unless condition', function () {
    $request = new UserRequest;

    $request->unless(
        fn () => false,
        function (UserRequest $request) {
            $request->headers()->add('X-Name', 'Alex');
        }
    );

    expect($request->headers()->all())->toHaveKey('X-Name', 'Alex');
    expect($request->headers()->all())->not->toHaveKey('X-Name', 'Sam');
});

test('you can provide a callback as the default value of the when condition', function () {
    $request = new UserRequest;

    $request->when(
        false,
        function (UserRequest $request) {
            $request->headers()->add('X-Name', 'Sam');
        },
        function (UserRequest $request) {
            $request->headers()->add('X-Name', 'Alex');
        }
    );

    expect($request->headers()->all())->toHaveKey('X-Name', 'Alex');
    expect($request->headers()->all())->not->toHaveKey('X-Name', 'Sam');
});

test('you can provide a callback as the default value of the unless condition', function () {
    $request = new UserRequest;

    $request->unless(
        true,
        function (UserRequest $request) {
            $request->headers()->add('X-Name', 'Sam');
        },
        function (UserRequest $request) {
            $request->headers()->add('X-Name', 'Alex');
        }
    );

    expect($request->headers()->all())->toHaveKey('X-Name', 'Alex');
    expect($request->headers()->all())->not->toHaveKey('X-Name', 'Sam');
});

test('it will pass the condition value as the second argument of the callable', function () {
    $request = new UserRequest;

    $request->when(true, function (UserRequest $request, mixed $value) {
        expect($value)->toBeBool();
        expect($value)->toBeTrue();
    });

    $request->unless(false, function (UserRequest $request, mixed $value) {
        expect($value)->toBeBool();
        expect($value)->toBeFalse();
    });
});

test('it will pass the condition value as the second argument of the default callable', function () {
    $request = new UserRequest;

    $request->when(
        false,
        function (UserRequest $request, mixed $value) {
            //
        },
        function (UserRequest $request, mixed $value) {
            expect($value)->toBeBool();
            expect($value)->toBeFalse();
        }
    );

    $request->unless(
        true,
        function (UserRequest $request, mixed $value) {
            //
        },
        function (UserRequest $request, mixed $value) {
            expect($value)->toBeBool();
            expect($value)->toBeTrue();
        }
    );
});
