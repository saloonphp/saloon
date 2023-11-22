<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use Saloon\Http\Faking\Fixture;
use Saloon\Http\PendingRequest;
use Saloon\Http\Faking\MockResponse;

interface HasFakeData
{
    /**
     * Get the fake data for the mocked request.
     *
     * @return array<mixed,mixed>|string|MockResponse|Fixture
     */
    public function getFakeData(PendingRequest $request): array|string|MockResponse|Fixture;
}
