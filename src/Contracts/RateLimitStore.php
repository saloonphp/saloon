<?php

namespace Saloon\Contracts;

use Saloon\Http\RateLimiting\Limit;

interface RateLimitStore
{
    /**
     * Hydrate the properties on the limit (hits, timestamp etc)
     *
     * @param \Saloon\Http\RateLimiting\Limit $limit
     * @return \Saloon\Http\RateLimiting\Limit
     */
    public function hydrateLimit(Limit $limit): Limit;

    /**
     * Commit the properties on the limit (hits, timestamp)
     *
     * @param \Saloon\Http\RateLimiting\Limit $limit
     * @return void
     */
    public function commitLimit(Limit $limit): void;
}
