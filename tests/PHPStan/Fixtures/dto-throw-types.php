<?php

declare(strict_types=1);

namespace Saloon\Tests\PHPStan\Fixtures;

use Saloon\Tests\Fixtures\Data\User;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Requests\TypedDtoRequest;

function dtoWithoutDeclaring(TestConnector $connector): User
{
    return $connector->send(new TypedDtoRequest)->dto();
}

function dtoOrFailWithoutDeclaring(TestConnector $connector): User
{
    return $connector->send(new TypedDtoRequest)->dtoOrFail();
}

/**
 * @throws \JsonException
 */
function dtoDeclaring(TestConnector $connector): User
{
    return $connector->send(new TypedDtoRequest)->dto();
}

function untypedDto(TestConnector $connector): mixed
{
    return $connector->send(new UserRequest)->dto();
}
