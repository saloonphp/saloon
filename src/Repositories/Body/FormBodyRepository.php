<?php

declare(strict_types=1);

namespace Saloon\Repositories\Body;

use Saloon\Contracts\PendingRequest;

class FormBodyRepository extends ArrayBodyRepository
{
    /**
     * Boot the FormBodyRepository trait
     *
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     * @return void
     */
    public function bootFormBodyRepository(PendingRequest $pendingRequest): void
    {
        $pendingRequest->headers()->add('Content-Type', 'application/x-www-form-urlencoded');
    }

    /**
     * Convert into a string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return http_build_query($this->all());
    }
}
