<?php

declare(strict_types=1);

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Tests\Fixtures\Data\User;
use Saloon\Exceptions\DataObjectException;
use Saloon\Contracts\DataObjects\WithResponse;
use Saloon\Tests\Fixtures\Requests\DTORequest;
use Saloon\Tests\Fixtures\Data\UserWithResponse;
use Saloon\Tests\Fixtures\Requests\DTOPropertyRequest;
use Saloon\Tests\Fixtures\Requests\DTOWithResponseRequest;
use Saloon\Tests\Fixtures\Requests\InvalidDTOPropertyRequest;

test('if a dto does not implement the WithResponse interface and HasResponse trait Saloon will not add the original response', function () {
    $mockClient = new MockClient([
        new MockResponse(['name' => 'Sammyjo20', 'actual_name' => 'Sam', 'twitter' => '@carre_sam']),
    ]);

    $request = new DTORequest();
    $response = $request->send($mockClient);
    $dto = $response->dto();

    expect($dto)->toBeInstanceOf(User::class);
    expect($dto)->not->toBeInstanceOf(WithResponse::class);
});

test('if a dto implements the WithResponse interface and HasResponse trait Saloon will add the original response', function () {
    $mockClient = new MockClient([
        new MockResponse(['name' => 'Sammyjo20', 'actual_name' => 'Sam', 'twitter' => '@carre_sam']),
    ]);

    $request = new DTOWithResponseRequest();
    $response = $request->send($mockClient);

    /** @var UserWithResponse $dto */
    $dto = $response->dto();

    expect($dto)->toBeInstanceOf(UserWithResponse::class);
    expect($dto)->toBeInstanceOf(WithResponse::class);
    expect($dto->getResponse())->toBe($response);
});

test('you can use the responseDataObject property to specify a dto', function () {
    $mockClient = new MockClient([
        new MockResponse(['name' => 'Sammyjo20', 'actual_name' => 'Sam', 'twitter' => '@carre_sam']),
    ]);

    $request = new DTOPropertyRequest();
    $response = $request->send($mockClient);

    /** @var UserWithResponse $dto */
    $dto = $response->dto();

    expect($dto)->toBeInstanceOf(UserWithResponse::class);
    expect($dto)->toBeInstanceOf(WithResponse::class);
    expect($dto->getResponse())->toBe($response);
});

test('if you use the responseDataObject property to specify a dto but dont implement FromSaloonResponse it will throw an exception', function () {
    $mockClient = new MockClient([
        new MockResponse(['name' => 'Sammyjo20', 'actual_name' => 'Sam', 'twitter' => '@carre_sam']),
    ]);

    $request = new InvalidDTOPropertyRequest();
    $response = $request->send($mockClient);

    $this->expectException(DataObjectException::class);
    $this->expectDeprecationMessage('When using the `responseDataObject` property the class must implement the Saloon\Contracts\DataObjects\FromResponse interface.');

    $response->dto();
});
