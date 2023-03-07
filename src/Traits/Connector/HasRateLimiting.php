<?php

namespace Saloon\Traits\Connector;

use Saloon\Contracts\PendingRequest;
use Saloon\Contracts\Response;
use Saloon\Http\RateLimiting\Limit;

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
        // We'll populate our limits here (todo: move to somewhere better)
        // Todo: Allow people to customise the name and not just the connector name

        $limits = array_filter($this->resolveLimits(), static fn (mixed $value) => $value instanceof Limit);

        if (empty($limits)) {
            return;
        }

        // We'll now run the "setConnectorName" on each of the limits because
        // this will allow the limit classes to populate the ID properly.

        // We'll now have an array of the limits with IDs that can be generated.

        $limits = array_map(function (Limit $limit) use ($pendingRequest) {
            return $limit->setConnectorName($pendingRequest->getConnector());
        }, $limits);

        $driver = null;

        $pendingRequest->middleware()->onRequest(function (PendingRequest $pendingRequest) use ($limits, $driver) {
            // Todo: Use the driver to check if we've already hit any of the limits
            // Todo: if($driver->hasReachedLimit())

            // Todo: We may have made the last request so we should iterate over each limit and see if we're on the end

            foreach ($limits as $limit) {
                // Todo: $driver->hydrateLimit($limit);

                if (! $limit->hasReachedLimit()) {
                    // Todo: $driver->commitLimit($limit)
                    continue;
                }

                // This will also throw the exception we need

                $this->storeLimit($driver, $limit);
            }
        });

        // Register the limit counter

        $pendingRequest->middleware()->onResponse(function (Response $response) use ($limits, $driver) {
            foreach ($limits as $limit) {
                // Todo: $driver->hydrateLimit($limit);

                $this->processLimit($response, $limit);

                if (! $limit->hasReachedLimit()) {
                    // Todo: $driver->commitLimit($limit)
                    continue;
                }

                $this->storeLimit($driver, $limit);
            }
        });

        // dd($limits[0]);
    }

    /**
     * Resolve the limits for the rate limiter
     *
     * @return array<\Saloon\Http\RateLimiting\Limit>
     */
    abstract protected function resolveLimits(): array;

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

    protected function storeLimit($driver, Limit $limit)
    {
        // Store the limit in the driver

        throw new \Exception('Hit limit!');
    }
}
