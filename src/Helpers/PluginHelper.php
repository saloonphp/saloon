<?php

declare(strict_types=1);

namespace Saloon\Helpers;

use ReflectionClass;
use Saloon\Contracts\Request;
use Saloon\Contracts\Connector;
use Saloon\Contracts\PendingRequest;

class PluginHelper
{
    /**
     * Boot a given plugin/trait
     *
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     * @param \Saloon\Contracts\Connector|Request $resource
     * @param class-string $trait
     * @return void
     * @throws \ReflectionException
     */
    public static function bootPlugin(PendingRequest $pendingRequest, Connector|Request $resource, string $trait): void
    {
        $traitReflection = new ReflectionClass($trait);

        $bootMethodName = 'boot' . $traitReflection->getShortName();

        if (! method_exists($resource, $bootMethodName)) {
            return;
        }

        $resource->{$bootMethodName}($pendingRequest);
    }
}
