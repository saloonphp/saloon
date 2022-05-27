<?php

namespace Sammyjo20\Saloon\Helpers;

use Sammyjo20\Saloon\Http\RequestPayload;
use Sammyjo20\Saloon\Http\SaloonConnector;
use Sammyjo20\Saloon\Http\SaloonRequest;

class PluginHelper
{
    /**
     * @param RequestPayload $requestPayload
     * @param SaloonConnector|SaloonRequest $resource
     * @param \ReflectionClass $trait
     * @return void
     */
    public static function bootPlugin(RequestPayload $requestPayload, SaloonConnector|SaloonRequest $resource, \ReflectionClass $trait): void
    {
        $bootMethodName = 'boot' . $trait->getShortName();

        if (! method_exists($resource, $bootMethodName)) {
            return;
        }

        $resource->{$bootMethodName}($requestPayload);
    }
}
