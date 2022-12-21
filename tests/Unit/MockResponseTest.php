<?php

declare(strict_types=1);

use Saloon\Http\Response;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Tests\Fixtures\Responses\UserData;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Responses\UserResponse;
use Saloon\Repositories\Body\StringBodyRepository;
use Saloon\Tests\Fixtures\Requests\UserRequestWithCustomResponse;

test('pulling a response from the sequence will return the correct response', function () {
    $responseA = MockResponse::make();
    $responseB = MockResponse::make([], 500);
    $responseC = MockResponse::make([], 500);

    $mockClient = new MockClient([$responseA, $responseB, $responseC]);

    expect($mockClient->getNextFromSequence()->getStatus())->toEqual($responseA->getStatus());
    expect($mockClient->getNextFromSequence()->getStatus())->toEqual($responseB->getStatus());
    expect($mockClient->getNextFromSequence()->getStatus())->toEqual($responseC->getStatus());
    expect($mockClient->isEmpty())->toBeTrue();
});

test('a mock response can have raw body data', function () {
    $response = MockResponse::make('xml', 200, ['Content-Type' => 'application/json']);

    expect($response->getHeaders()->all())->toEqual(['Content-Type' => 'application/json']);
    expect($response->getStatus())->toEqual(200);
    expect($response->getBody())->toBeInstanceOf(StringBodyRepository::class);
    expect($response->getBody()->all())->toEqual('xml');
});

test('a response can have a method added to it', function () {
    $mockClient = new MockClient([MockResponse::make([])]);
    $request = new UserRequest();

    Response::macro('yeehaw', function () {
        return 'Yee-haw!';
    });

    $response = connector()->send($request, $mockClient);

    expect($response->yeehaw())->toEqual('Yee-haw!');
});

test('a response can be a custom response class', function () {
    $mockClient = new MockClient([MockResponse::make(['foo' => 'bar'])]);
    $request = new UserRequestWithCustomResponse();

    $response = connector()->send($request, $mockClient);

    expect($response)->toBeInstanceOf(UserResponse::class);
    expect($response)->customCastMethod()->toBeInstanceOf(UserData::class);
    expect($response)->foo()->toBe('bar');
});
