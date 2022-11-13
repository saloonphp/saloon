<?php declare(strict_types=1);

namespace Saloon\Helpers;

use Saloon\Http\Request;
use Saloon\Http\Connector;
use Saloon\Exceptions\InvalidRequestException;

class RequestHelper
{
    /**
     * Call a request from a connector.
     *
     * @param Connector $connector
     * @param string $request
     * @param array $arguments
     * @return Request
     * @throws InvalidRequestException
     * @throws \ReflectionException
     */
    public static function callFromConnector(Connector $connector, string $request, array $arguments = []): Request
    {
        $isValidRequest = ReflectionHelper::isSubclassOf($request, Request::class);

        if (! $isValidRequest) {
            throw new InvalidRequestException($request);
        }

        return (new $request(...$arguments))->setConnector($connector);
    }
}
