<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class UserRequest extends Request
{
    /**
     * Define the HTTP method.
     */
    protected Method $method = Method::GET;

    /**
     * Define the endpoint for the request.
     */
    public function resolveEndpoint(): string
    {
        return '/user';
    }

    public function __construct(public ?int $userId = null, public ?int $groupId = null)
    {
        //
    }
}
