<?php

declare(strict_types=1);

namespace Saloon\Exceptions;

use Saloon\Contracts\Connector;

class MethodNotFoundException extends SaloonException
{
    /**
     * Exception
     *
     * @param string $method
     * @param Connector $connector
     */
    public function __construct(string $method, Connector $connector)
    {
        parent::__construct(sprintf('Unable to find the "%s" method on the request class or the "%s" connector.', $method, get_class($connector)));
    }
}
