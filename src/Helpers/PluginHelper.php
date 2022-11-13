<?php declare(strict_types=1);

namespace Saloon\Helpers;

use ReflectionClass;
use Saloon\Http\Request;
use Saloon\Http\Connector;
use Saloon\Http\PendingRequest;

class PluginHelper
{
    /**
     * Boot a given plugin/trait
     *
     * @param PendingRequest $pendingRequest
     * @param Connector|Request $resource
     * @param string $trait
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