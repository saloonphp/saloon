<?php declare(strict_types=1);

namespace Sammyjo20\Saloon\Helpers;

use ReflectionClass;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Http\SaloonConnector;
use Sammyjo20\Saloon\Http\PendingSaloonRequest;

class PluginHelper
{
    /**
     * Boot a given plugin/trait
     *
     * @param PendingSaloonRequest $pendingRequest
     * @param SaloonConnector|SaloonRequest $resource
     * @param string $trait
     * @return void
     * @throws \ReflectionException
     */
    public static function bootPlugin(PendingSaloonRequest $pendingRequest, SaloonConnector|SaloonRequest $resource, string $trait): void
    {
        $traitReflection = new ReflectionClass($trait);

        $bootMethodName = 'boot' . $traitReflection->getShortName();

        if (! method_exists($resource, $bootMethodName)) {
            return;
        }

        $resource->{$bootMethodName}($pendingRequest);
    }
}
