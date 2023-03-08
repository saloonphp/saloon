<?php

declare(strict_types=1);

namespace Saloon\Helpers;

use Saloon\Contracts\Request;
use Saloon\Contracts\Connector;
use Saloon\Http\RateLimiting\Limit;

class LimitHelper
{
    /**
     * Hydrate the limits
     *
     * @param array<\Saloon\Http\RateLimiting\Limit> $limits
     * @param \Saloon\Contracts\Connector|\Saloon\Contracts\Request $connectorOrRequest
     * @return array<\Saloon\Http\RateLimiting\Limit>
     * @throws \ReflectionException
     */
    public static function hydrateLimits(array $limits, Connector|Request $connectorOrRequest): array
    {
        $limits = array_filter($limits, static fn (mixed $value) => $value instanceof Limit);

        if (empty($limits)) {
            return [];
        }

        $limits = Arr::mapWithKeys($limits, static function (Limit $limit, int|string $key) use ($connectorOrRequest) {
            return [$key => is_string($key) ? $limit->id($key) : $limit->setObjectName($connectorOrRequest)];
        });

        return array_values($limits);
    }
}
