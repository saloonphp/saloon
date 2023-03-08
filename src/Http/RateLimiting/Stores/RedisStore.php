<?php

declare(strict_types=1);

namespace Saloon\Http\RateLimiting\Stores;

use Predis\Client;
use Saloon\Http\RateLimiting\Limit;
use Saloon\Contracts\RateLimitStore;

class RedisStore implements RateLimitStore
{
    /**
     * Constructor
     *
     * @param \Predis\Client $redis
     */
    public function __construct(protected Client $redis)
    {
        //
    }

    /**
     * Hydrate the properties on the limit (hits, timestamp etc)
     *
     * @param \Saloon\Http\RateLimiting\Limit $limit
     * @return \Saloon\Http\RateLimiting\Limit
     * @throws \JsonException
     */
    public function hydrateLimit(Limit $limit): Limit
    {
        $value = $this->redis->get($limit->getId());

        if (is_null($value)) {
            return $limit;
        }

        return $limit->unserializeStoreData($value);
    }

    /**
     * Commit the properties on the limit (hits, timestamp)
     *
     * @param \Saloon\Http\RateLimiting\Limit $limit
     * @return void
     * @throws \JsonException
     */
    public function commitLimit(Limit $limit): void
    {
        $this->redis->setex(
            key: $limit->getId(),
            seconds: $limit->getRemainingSeconds(),
            value: $limit->serializeStoreData()
        );
    }
}
