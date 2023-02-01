<?php

declare(strict_types=1);

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Tests\Fixtures\Data\User;
use Saloon\Tests\Fixtures\Data\ApiResponse;
use Saloon\Tests\Fixtures\Requests\DTORequest;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Connectors\DtoConnector;

test('it can cast to a dto that is defined on the request', function () {
    $mockClient = new MockClient([
        new MockResponse(['name' => 'Sammyjo20', 'actual_name' => 'Sam Carré', 'twitter' => '@carre_sam']),
    ]);

    $response = connector()->send(new DTORequest, $mockClient);
    $dto = $response->dto();
    $json = $response->json();

    expect($response->isMocked())->toBeTrue();
    expect($dto)->toBeInstanceOf(User::class);
    expect($dto)->name->toEqual($json['name']);
    expect($dto)->actualName->toEqual($json['actual_name']);
    expect($dto)->twitter->toEqual($json['twitter']);
});

test('it can cast to a dto that is defined on a connector', function () {
    $mockClient = new MockClient([
        new MockResponse(['name' => 'Sammyjo20', 'actual_name' => 'Sam Carré', 'twitter' => '@carre_sam']),
    ]);

    $connector = new DtoConnector;

    $response = $connector->send(new UserRequest, $mockClient);
    $dto = $response->dto();

    expect($dto)->toBeInstanceOf(ApiResponse::class);
    expect($dto)->data->toEqual($response->json());
});

test('the request dto will be returned as a higher priority than the connector dto', function () {
    $mockClient = new MockClient([
        new MockResponse(['name' => 'Sammyjo20', 'actual_name' => 'Sam Carré', 'twitter' => '@carre_sam']),
    ]);

    $connector = new DtoConnector;

    $response = $connector->send(new DTORequest, $mockClient);
    $dto = $response->dto();
    $json = $response->json();

    expect($dto)->toBeInstanceOf(User::class);
    expect($dto)->name->toEqual($json['name']);
    expect($dto)->actualName->toEqual($json['actual_name']);
    expect($dto)->twitter->toEqual($json['twitter']);
});

test('you can use the dtoOrFail method to throw an exception if the response has failed', function () {
    $mockClient = new MockClient([
        new MockResponse(['message' => 'Server Error'], 500),
    ]);

    $response = connector()->send(new DTORequest, $mockClient);

    $this->expectException(LogicException::class);
    $this->expectExceptionMessage('Unable to create data transfer object as the response has failed.');

    $response->dtoOrFail();
});
