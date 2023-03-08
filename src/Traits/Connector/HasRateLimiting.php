<?php

declare(strict_types=1);

namespace Saloon\Traits\Connector;

use Saloon\Contracts\Response;
use Saloon\Helpers\LimitHelper;
use Saloon\Http\RateLimiting\Limit;
use Saloon\Contracts\PendingRequest;
use Saloon\Contracts\RateLimitStore;
use Saloon\Exceptions\RateLimitReachedException;

trait HasRateLimiting
{
    /**
     * Boot the has rate limiting trait
     *
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     * @return void
     * @throws \ReflectionException
     */
    public function bootHasRateLimiting(PendingRequest $pendingRequest): void
    {
        // Todo: Allow people to customise the name and not just the connector name

        // We'll now run the "setConnectorName" on each of the limits because
        // this will allow the limit classes to populate the ID properly.

        $limits = LimitHelper::hydrateLimits($this->resolveLimits(), $this);

        // We'll now have an array of the limits with IDs that can be generated.

        $store = $this->resolveRateLimitStore();

        $pendingRequest->middleware()->onRequest(function (PendingRequest $pendingRequest) use ($limits, $store) {
            // We'll check here if we have reached the rate limit - if we have
            // then we need to throw the limit exception

            if ($limit = $this->getExceededLimit()) {
                $this->throwLimitException($limit);
            }
        });

        // Register the limit counter

        $pendingRequest->middleware()->onResponse(function (Response $response) use ($limits, $store) {
            $limitReached = null;

            foreach ($limits as $limit) {
                $limit = $store->hydrateLimit($limit);

                $this->processLimit($response, $limit);

                $store->commitLimit($limit);

                // We should set a variable here so even if the first limiter gets
                // thrown, we will commit every limiter.

                // Todo:
                // Problem Scenario: Say that the response is 429 and our code runs the "exceeded"
                // method. The issue with this method is that it will commit the max for every
                // limiter, not just the one we want - which could make it trickier because
                // it may only be hitting a specific limiter, like 429 too many attempts in
                // the current hour, we don't have to rule out the whole day

                if ($limit->hasReachedLimit()) {
                    $limitReached = $limit;
                }
            }

            if ($limitReached) {
                $this->throwLimitException($limitReached);
            }
        });
    }

    /**
     * Resolve the limits for the rate limiter
     *
     * @return array<\Saloon\Http\RateLimiting\Limit>
     */
    abstract protected function resolveLimits(): array;

    /**
     * Resolve the rate limit store
     *
     * @return RateLimitStore
     */
    abstract protected function resolveRateLimitStore(): RateLimitStore;

    /**
     * Process the limit, can be extended
     *
     * @param \Saloon\Contracts\Response $response
     * @param \Saloon\Http\RateLimiting\Limit $limit
     * @return void
     */
    protected function processLimit(Response $response, Limit $limit): void
    {
        if ($response->status() === 429) {
            $limit->exceeded();

            return;
        }

        $limit->hit();
    }

    /**
     * Throw the limit exception
     *
     * @param \Saloon\Http\RateLimiting\Limit $limit
     * @return void
     * @throws \Saloon\Exceptions\RateLimitReachedException
     */
    protected function throwLimitException(Limit $limit): void
    {
        throw new RateLimitReachedException($limit);
    }

    /**
     * Get the first limit that has exceeded
     *
     * @return \Saloon\Http\RateLimiting\Limit|null
     * @throws \ReflectionException
     */
    public function getExceededLimit(): ?Limit
    {
        $limits = LimitHelper::hydrateLimits($this->resolveLimits(), $this);

        if (empty($limits)) {
            return null;
        }

        $store = $this->resolveRateLimitStore();

        foreach ($limits as $limit) {
            $limit = $store->hydrateLimit($limit);

            if ($limit->hasReachedLimit()) {
                return $limit;
            }
        }

        return null;
    }

    /**
     * Check if we have reached the rate limit
     *
     * @return bool
     * @throws \ReflectionException
     */
    public function hasReachedRateLimit(): bool
    {
        return $this->getExceededLimit() instanceof Limit;
    }
}
