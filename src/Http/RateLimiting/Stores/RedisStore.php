<?php

namespace Saloon\Http\RateLimiting\Stores;

use Predis\Client;
use Saloon\Contracts\RateLimitStore;
use Saloon\Http\RateLimiting\Limit;

class RedisStore implements RateLimitStore
{
    // Todo: Might not need this, consider removing


    public function __construct(protected Client $redis)
    {
        //
    }

    /**
     * Hydrate the properties on the limit (hits, timestamp etc)
     *
     * @param \Saloon\Http\RateLimiting\Limit $limit
     * @return \Saloon\Http\RateLimiting\Limit
     */
    public function hydrateLimit(Limit $limit): Limit
    {
        $value = $this->redis->get($limit->getId());

        $limit->setHits($value ?? 0);

        return $limit;
    }

    /**
     * Commit the properties on the limit (hits, timestamp)
     *
     * @param \Saloon\Http\RateLimiting\Limit $limit
     * @return void
     */
    public function commitLimit(Limit $limit): void
    {
        if (! $this->redis->exists($limit->getId())) {
            $this->redis->setex($limit->getId(), $limit->getReleaseInSeconds(), $limit->getHits());
        }

        // Todo: Work out a better way to do this with less calls to redis

        $this->redis->setex($limit->getId(), $this->redis->ttl($limit->getId()), $limit->getHits());

        // TODO: Implement commitLimit() method.
    }
}
