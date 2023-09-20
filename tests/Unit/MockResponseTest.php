<?php

declare(strict_types=1);

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Tests\Fixtures\Responses\UserData;
use Saloon\Tests\Fixtures\Responses\UserResponse;
use Saloon\Repositories\Body\StringBodyRepository;
use Saloon\Tests\Fixtures\Requests\UserRequestWithCustomResponse;

test('pulling a response from the sequence will return the correct response', function () {
    $responseA = MockResponse::make();
    $responseB = MockResponse::make([], 500);
    $responseC = MockResponse::make([], 500);

    $mockClient = new MockClient([$responseA, $responseB, $responseC]);

    expect($mockClient->getNextFromSequence()->status())->toEqual($responseA->status());
    expect($mockClient->getNextFromSequence()->status())->toEqual($responseB->status());
    expect($mockClient->getNextFromSequence()->status())->toEqual($responseC->status());
    expect($mockClient->isEmpty())->toBeTrue();
});

test('a mock response can have raw body data', function () {
    $response = MockResponse::make('xml', 200, ['Content-Type' => 'application/json']);

    expect($response->headers()->all())->toEqual(['Content-Type' => 'application/json']);
    expect($response->status())->toEqual(200);
    expect($response->body())->toBeInstanceOf(StringBodyRepository::class);
    expect($response->body()->all())->toEqual('xml');
});

test('a response can be a custom response class', function () {
    $mockClient = new MockClient([MockResponse::make(['foo' => 'bar'])]);
    $request = new UserRequestWithCustomResponse();

    $response = connector()->send($request, $mockClient);

    expect($response)->toBeInstanceOf(UserResponse::class);
    expect($response)->customCastMethod()->toBeInstanceOf(UserData::class);
    expect($response)->foo()->toBe('bar');
});
