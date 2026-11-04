<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Tests\Fixtures\Data\User;

class TypedDtoRequest extends Request
{
    protected Method $method = Method::GET;

    public function resolveEndpoint(): string
    {
        return '/user';
    }

    /**
     * @throws \JsonException
     */
    public function createDtoFromResponse(Response $response): User
    {
        return User::fromSaloon($response);
    }
}
