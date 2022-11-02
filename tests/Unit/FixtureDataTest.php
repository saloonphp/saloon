<?php declare(strict_types=1);

namespace Sammyjo20\Saloon\Tests\Unit;

use Sammyjo20\Saloon\Data\FixtureData;
use Sammyjo20\Saloon\Http\MockResponse;

test('you can create a fixture data object from a file string', function () {
    $data = [
        'statusCode' => 200,
        'headers' => [
            'Content-Type' => 'application/json',
        ],
        'data' => [
            'name' => 'Sam',
        ],
    ];

    $fixtureData = FixtureData::fromFile(json_encode($data));

    expect($fixtureData->statusCode)->toEqual($data['statusCode']);
    expect($fixtureData->headers)->toEqual($data['headers']);
    expect($fixtureData->data)->toEqual($data['data']);
});

test('you can create a mock response from fixture data', function () {
    $data = [
        'statusCode' => 200,
        'headers' => [
            'Content-Type' => 'application/json',
        ],
        'data' => [
            'name' => 'Sam',
        ],
    ];

    $fixtureData = FixtureData::fromFile(json_encode($data));
    $mockResponse = $fixtureData->toMockResponse();

    expect($mockResponse)->toEqual(new MockResponse($data['statusCode'], $data['data'], $data['headers']));
});

test('you can json serialize the fixture data or convert it into a file', function () {
    $data = [
        'statusCode' => 200,
        'headers' => [
            'Content-Type' => 'application/json',
        ],
        'data' => [
            'name' => 'Sam',
        ],
    ];

    $fixtureData = FixtureData::fromFile(json_encode($data));

    $serialized = json_encode($fixtureData);

    expect($serialized)->toEqual(json_encode($data));
    expect($fixtureData->toFile())->toEqual($serialized);
});
