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

        return array_map(static function (Limit $limit) use ($connectorOrRequest) {
            return $limit->setObjectName($connectorOrRequest);
        }, $limits);
    }
}
