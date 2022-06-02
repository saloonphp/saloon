<?php

namespace Sammyjo20\Saloon\Helpers;

use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Http\SaloonConnector;
use Sammyjo20\Saloon\Http\PendingSaloonRequest;

class PluginHelper
{
    /**
     * @param PendingSaloonRequest $requestPayload
     * @param SaloonConnector|SaloonRequest $resource
     * @param \ReflectionClass $trait
     * @return void
     */
    public static function bootPlugin(PendingSaloonRequest $requestPayload, SaloonConnector|SaloonRequest $resource, \ReflectionClass $trait): void
    {
        $bootMethodName = 'boot' . $trait->getShortName();

        if (! method_exists($resource, $bootMethodName)) {
            return;
        }

        $resource->{$bootMethodName}($requestPayload);
    }
}
