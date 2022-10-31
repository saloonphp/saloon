<?php

namespace Sammyjo20\Saloon\Helpers;

use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Http\SaloonConnector;
use Sammyjo20\Saloon\Http\PendingSaloonRequest;

class PluginHelper
{
    /**
     * Boot a given plugin/trait
     *
     * @param PendingSaloonRequest $requestPayload
     * @param SaloonConnector|SaloonRequest $resource
     * @param string $trait
     * @return void
     * @throws \ReflectionException
     */
    public static function bootPlugin(PendingSaloonRequest $requestPayload, SaloonConnector|SaloonRequest $resource, string $trait): void
    {
        $traitReflection = new \ReflectionClass($trait);

        $bootMethodName = 'boot' . $traitReflection->getShortName();

        if (! method_exists($resource, $bootMethodName)) {
            return;
        }

        $resource->{$bootMethodName}($requestPayload);
    }
}
