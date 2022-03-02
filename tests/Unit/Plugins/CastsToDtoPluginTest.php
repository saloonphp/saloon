<?php

use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Tests\Fixtures\Data\User;
use Sammyjo20\Saloon\Tests\Fixtures\Data\ApiResponse;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\DTORequest;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\UserRequest;
use Sammyjo20\Saloon\Tests\Fixtures\Connectors\DtoConnector;

test('it can cast to a dto that is defined on the request', function () {
    $mockClient = new MockClient([
        new MockResponse(['name' => 'Sammyjo20', 'actual_name' => 'Sam Carré', 'twitter' => '@carre_sam']),
    ]);

    $response = DTORequest::make()->send($mockClient);
    $dto = $response->dto();
    $json = $response->json();

    expect($dto)->toBeInstanceOf(User::class);
    expect($dto)->name->toEqual($json['name']);
    expect($dto)->actualName->toEqual($json['actual_name']);
    expect($dto)->twitter->toEqual($json['twitter']);
});

test('it can cast to a dto that is defined on a connector', function () {
    $mockClient = new MockClient([
        new MockResponse(['name' => 'Sammyjo20', 'actual_name' => 'Sam Carré', 'twitter' => '@carre_sam']),
    ]);

    $response = UserRequest::make()->setLoadedConnector(new DtoConnector())->send($mockClient);
    $dto = $response->dto();

    expect($dto)->toBeInstanceOf(ApiResponse::class);
    expect($dto)->data->toEqual($response->json());
});

test('the request dto will be returned as a higher priority than the connector dto', function () {
    $mockClient = new MockClient([
        new MockResponse(['name' => 'Sammyjo20', 'actual_name' => 'Sam Carré', 'twitter' => '@carre_sam']),
    ]);

    $response = DTORequest::make()->setLoadedConnector(new DtoConnector())->send($mockClient);
    $dto = $response->dto();
    $json = $response->json();

    expect($dto)->toBeInstanceOf(User::class);
    expect($dto)->name->toEqual($json['name']);
    expect($dto)->actualName->toEqual($json['actual_name']);
    expect($dto)->twitter->toEqual($json['twitter']);
});
