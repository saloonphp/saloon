<?php declare(strict_types=1);

use Saloon\Http\Request;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Responses\Response;
use Saloon\Tests\Fixtures\Responses\UserData;
use Saloon\Tests\Fixtures\Requests\MockRequest;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Responses\UserResponse;
use Saloon\Tests\Fixtures\Requests\UserRequestWithCustomResponse;

test('pulling a response from the sequence will return the correct response', function () {
    $responseA = MockResponse::make([], 200);
    $responseB = MockResponse::make([], 201);
    $responseC = MockResponse::make([], 500);

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
    $response = MockResponse::make('xml', 200, ['Content-Type' => 'application/json']);

    expect($response->getHeaders())->toEqual(['Content-Type' => 'application/json']);
    expect($response->getConfig())->toEqual([]);
    expect($response->getStatus())->toEqual(200);
    expect($response->getFormattedData())->toEqual('xml');
});

test('a response can have a method added to it', function () {
    $mockClient = new MockClient([MockResponse::make([], 200)]);
    $request = new UserRequest();

    $request->addResponseInterceptor(function (Request $request, Response $response) {
        $response::macro('yeehaw', function () {
            return 'Yee-haw!';
        });

        return $response;
    });

    $response = $request->send($mockClient);

    expect($response->yeehaw())->toEqual('Yee-haw!');
});

test('a response can be a custom response class', function () {
    $mockClient = new MockClient([MockResponse::make(['foo' => 'bar'], 200)]);
    $request = new UserRequestWithCustomResponse();

    $response = $request->send($mockClient);

    expect($response)->toBeInstanceOf(UserResponse::class);
    expect($response)->customCastMethod()->toBeInstanceOf(UserData::class);
    expect($response)->foo()->toBe('bar');
});
