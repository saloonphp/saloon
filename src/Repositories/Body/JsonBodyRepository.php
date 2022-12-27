<?php

declare(strict_types=1);

namespace Saloon\Repositories\Body;

use Saloon\Contracts\PendingRequest;

class JsonBodyRepository extends ArrayBodyRepository
{
    /**
     * Boot the JsonBodyRepository trait
     *
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     * @return void
     */
    public function bootJsonBodyRepository(PendingRequest $pendingRequest): void
    {
        $pendingRequest->headers()->add('Content-Type', 'application/json');
    }

    /**
     * Convert the body repository into a string.
     *
     * @return string
     * @throws \JsonException
     */
    public function __toString(): string
    {
        return json_encode($this->all(), JSON_THROW_ON_ERROR);
    }
}
