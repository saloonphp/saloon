<?php

declare(strict_types=1);

use Saloon\Http\Response;
use Saloon\Tests\Fixtures\Data\User;
use Saloon\Tests\Fixtures\Data\IntoUser;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Data\IntoUserWithResponse;

test('can create a dto using the into method on a request', function () {
    $connector = connector();
    $request = new UserRequest;

    $user = $connector->send($request)->into(IntoUser::class);

    expect($user)->toBeInstanceOf(IntoUser::class);
    expect($user->name)->toEqual('Sammyjo20');
});

test('if the class does not implement the dto interface it will throw an exception', function () {
    $connector = connector();
    $request = new UserRequest;

    $connector->send($request)->into(User::class);
})->throws(InvalidArgumentException::class, 'The class provided must implement the Saloon\Contracts\DataObjects\DataTransferObject interface.');

test('if the class implements the with response interface it will populate the response', function () {
    $connector = connector();
    $request = new UserRequest;

    $user = $connector->send($request)->into(IntoUserWithResponse::class);

    expect($user)->toBeInstanceOf(IntoUserWithResponse::class);
    expect($user->name)->toEqual('Sammyjo20');
    expect($user->getResponse())->toBeInstanceOf(Response::class);
});
