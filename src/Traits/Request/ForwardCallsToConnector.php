<?php declare(strict_types=1);

namespace Saloon\Traits\Request;

use Saloon\Exceptions\MethodNotFoundException;
use Saloon\Exceptions\InvalidConnectorException;

trait ForwardCallsToConnector
{
    /**
     * Dynamically proxy other methods to the connector.
     *
     * @param $method
     * @param $parameters
     * @return mixed
     * @throws InvalidConnectorException
     * @throws MethodNotFoundException
     */
    public function __call($method, $parameters)
    {
        $connector = $this->connector();

        if (method_exists($connector, $method) === false) {
            throw new MethodNotFoundException($method, $connector);
        }

        return $connector->{$method}(...$parameters);
    }
}
