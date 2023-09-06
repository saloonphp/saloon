<?php

declare(strict_types=1);

namespace Saloon\Helpers;

use Saloon\Contracts\Request;
use Saloon\Contracts\Connector;

/**
 * @internal
 */
final class RetryHelper
{
    /**
     * Get max tries on a connector or request
     */
    public static function getMaxTries(Connector $connector, Request $request): int
    {
        $maxTries = 1;

        if (property_exists($connector, 'tries') && is_int($connector->tries)) {
            $maxTries = $connector->tries;
        }

        if (property_exists($request, 'tries') && is_int($request->tries)) {
            $maxTries = $request->tries;
        }

        if ($maxTries <= 0) {
            $maxTries = 1;
        }

        return $maxTries;
    }

    /**
     * Get retry interval on a connector or request
     */
    public static function getRetryInterval(Connector $connector, Request $request): int
    {
        $retryInterval = 0;

        if (property_exists($connector, 'retryInterval') && is_int($connector->retryInterval)) {
            $retryInterval = $connector->retryInterval;
        }

        if (property_exists($request, 'retryInterval') && is_int($request->retryInterval)) {
            $retryInterval = $request->retryInterval;
        }

        if ($retryInterval <= 0) {
            $retryInterval = 0;
        }

        return $retryInterval;
    }

    /**
     * Should throw on max tries
     */
    public static function getThrowOnMaxTries(Connector $connector, Request $request): bool
    {
        $throwOnMaxTries = true;

        if (property_exists($connector, 'throwOnMaxTries') && is_bool($connector->throwOnMaxTries)) {
            $throwOnMaxTries = $connector->throwOnMaxTries;
        }

        if (property_exists($request, 'throwOnMaxTries') && is_bool($request->throwOnMaxTries)) {
            $throwOnMaxTries = $request->throwOnMaxTries;
        }

        return $throwOnMaxTries;
    }
}
