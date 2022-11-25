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

    $response = DTORequest::make()->send($mockClient);
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

    $response = UserRequest::make()->setConnector(new DtoConnector())->send($mockClient);
    $dto = $response->dto();

    expect($dto)->toBeInstanceOf(ApiResponse::class);
    expect($dto)->data->toEqual($response->json());
});

test('the request dto will be returned as a higher priority than the connector dto', function () {
    $mockClient = new MockClient([
        new MockResponse(['name' => 'Sammyjo20', 'actual_name' => 'Sam Carré', 'twitter' => '@carre_sam']),
    ]);

    $response = DTORequest::make()->setConnector(new DtoConnector())->send($mockClient);
    $dto = $response->dto();
    $json = $response->json();

    expect($dto)->toBeInstanceOf(User::class);
    expect($dto)->name->toEqual($json['name']);
    expect($dto)->actualName->toEqual($json['actual_name']);
    expect($dto)->twitter->toEqual($json['twitter']);
});
