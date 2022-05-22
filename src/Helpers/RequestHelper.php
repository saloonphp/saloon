<?php

namespace Sammyjo20\Saloon\Helpers;

use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Http\SaloonConnector;
use Sammyjo20\Saloon\Exceptions\SaloonInvalidRequestException;

class RequestHelper
{
    /**
     * Call a request from a connector.
     *
     * @param SaloonConnector $connector
     * @param string $request
     * @param array $arguments
     * @return mixed
     * @throws SaloonInvalidRequestException
     * @throws \ReflectionException
     */
    public static function callFromConnector(SaloonConnector $connector, string $request, array $arguments = [])
    {
        $isValidRequest = ReflectionHelper::isSubclassOf($request, SaloonRequest::class);

        if (! $isValidRequest) {
            throw new SaloonInvalidRequestException($request);
        }

        return (new $request(...$arguments))->setConnector($connector);
    }
}
