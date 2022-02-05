<?php

namespace Sammyjo20\Saloon\Exceptions;

use Sammyjo20\Saloon\Http\SaloonConnector;

class SaloonMethodNotFoundException extends SaloonException
{
    public function __construct(string $method, SaloonConnector $connector)
    {
        parent::__construct(sprintf('Unable to find the "%s" method on the request class or the "%s" connector.', $method, get_class($connector)));
    }
}
