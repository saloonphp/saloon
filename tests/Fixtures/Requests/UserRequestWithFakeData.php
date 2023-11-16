<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\PendingRequest;
use Saloon\Contracts\HasFakeData;

class UserRequestWithFakeData extends Request implements HasFakeData
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

    /**
     * Get the fake data for the mocked request.
     */
    public function getFakeData(PendingRequest $request): mixed
    {
        return ['Sam'];
    }
}
