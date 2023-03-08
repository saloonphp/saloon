<?php

declare(strict_types=1);

namespace Saloon\Http\RateLimiting\Stores;

use Predis\Client;
use Saloon\Http\RateLimiting\Limit;
use Saloon\Contracts\RateLimitStore;

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
     * @throws \JsonException
     */
    public function hydrateLimit(Limit $limit): Limit
    {
        $value = $this->redis->get($limit->getId());

        if (is_null($value)) {
            return $limit;
        }

        $data = json_decode($value, false, 512, JSON_THROW_ON_ERROR);

        $limit->setHits($data->hits);
        $limit->setExpiryTimestamp($data->timestamp);

        return $limit;
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
        $remainingSeconds = round($limit->getExpiryTimestamp() - microtime(true));

        $data = [
            'timestamp' => $limit->getExpiryTimestamp(),
            'hits' => $limit->getHits(),
        ];

        $this->redis->setex($limit->getId(), $remainingSeconds, json_encode($data, JSON_THROW_ON_ERROR));
    }
}
