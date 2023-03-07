<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Connectors;

use Predis\Client;
use Saloon\Contracts\PendingRequest;
use Saloon\Contracts\RateLimitStore;
use Saloon\Http\Connector;
use Saloon\Http\RateLimiting\Limit;
use Saloon\Http\RateLimiting\Stores\RedisStore;
use Saloon\Traits\Connector\HasRateLimiting;
use Saloon\Traits\Plugins\AcceptsJson;

class RateLimitedConnector extends Connector
{
    use AcceptsJson;
    use HasRateLimiting;

    /**
     * Define the base url of the api.
     *
     * @return string
     */
    public function resolveBaseUrl(): string
    {
        return apiUrl();
    }

    /**
     * Define the base headers that will be applied in every request.
     *
     * @return string[]
     */
    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
        ];
    }

    /**
     * Resolve the limits for the rate limiter
     *
     * @return array<\Saloon\Http\RateLimiting\Limit>
     */
    protected function resolveLimits(): array
    {
        // We'll only allow 8 requests

        return [
            Limit::allow(10)->everyMinute(),
            Limit::allow(20)->untilMidnightTonight(),
        ];
    }

    /**
     * Resolve the rate limit store
     *
     * @return RateLimitStore
     */
    protected function resolveRateLimitStore(): RateLimitStore
    {
        return new RedisStore(new Client);
    }
}
