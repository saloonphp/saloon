<?php declare(strict_types=1);

namespace Sammyjo20\Saloon\Traits\Request;

use Sammyjo20\Saloon\Exceptions\SaloonMethodNotFoundException;
use Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException;

trait ForwardCallsToConnector
{
    /**
     * Dynamically proxy other methods to the connector.
     *
     * @param $method
     * @param $parameters
     * @return mixed
     * @throws SaloonInvalidConnectorException
     * @throws SaloonMethodNotFoundException
     */
    public function __call($method, $parameters)
    {
        $connector = $this->connector();

        if (method_exists($connector, $method) === false) {
            throw new SaloonMethodNotFoundException($method, $connector);
        }

        return $connector->{$method}(...$parameters);
    }
}
