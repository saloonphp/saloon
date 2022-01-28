<?php

use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Http\SaloonResponse;
use Sammyjo20\Saloon\Tests\Resources\Responses\UserData;
use Sammyjo20\Saloon\Tests\Resources\Requests\MockRequest;
use Sammyjo20\Saloon\Tests\Resources\Requests\UserRequest;
use Sammyjo20\Saloon\Tests\Resources\Requests\UserRequestWithCustomResponse;
use Sammyjo20\Saloon\Tests\Resources\Responses\UserResponse;

test('pulling a response from the sequence will return the correct response', function () {
    $responseA = new MockResponse([], 200);
    $responseB = new MockResponse([], 201);
    $responseC = new MockResponse([], 500);

    $mockClient = new MockClient([$responseA, $responseB, $responseC]);

    expect($mockClient->getNextFromSequence()->getStatus())->toEqual($responseA->getStatus());
    expect($mockClient->getNextFromSequence()->getStatus())->toEqual($responseB->getStatus());
    expect($mockClient->getNextFromSequence()->getStatus())->toEqual($responseC->getStatus());
    expect($mockClient->isEmpty())->toBeTrue();
});

test('a mock response can be created from a request', function () {
    $request = new MockRequest;
    $response = MockResponse::fromRequest($request, 200);

    expect($response->getHeaders())->toEqual(array_merge($request->getHeaders(), ['Content-Type' => 'application/json']));
    expect($response->getConfig())->toEqual($request->getConfig());
    expect($response->getStatus())->toEqual(200);
});

test('a mock response can have raw body data', function () {
    $response = new MockResponse('xml', 200, ['Content-Type' => 'application/json']);

    expect($response->getHeaders())->toEqual(['Content-Type' => 'application/json']);
    expect($response->getConfig())->toEqual([]);
    expect($response->getStatus())->toEqual(200);
    expect($response->getFormattedData())->toEqual('xml');
});

test('a response can have a method added to it', function () {
    $mockClient = new MockClient([new MockResponse([], 200)]);
    $request = new UserRequest();

    $request->addResponseInterceptor(function (SaloonRequest $request, SaloonResponse $response) {
        $response::macro('yeehaw', function () {
            return 'Yee-haw!';
        });

        return $response;
    });

    $response = $request->send($mockClient);

    expect($response->yeehaw())->toEqual('Yee-haw!');
});

test('a response can be a custom response class', function () {
    $mockClient = new MockClient([new MockResponse(['foo' => 'bar'], 200)]);
    $request = new UserRequestWithCustomResponse();

    $response = $request->send($mockClient);

    expect($response)->toBeInstanceOf(UserResponse::class);
    expect($response)->customCastMethod()->toBeInstanceOf(UserData::class);
    expect($response)->foo()->toBe('bar');
});
