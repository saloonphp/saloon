<?php

namespace Sammyjo20\Saloon\Exceptions;

use \Exception;
use Sammyjo20\Saloon\Http\SaloonConnector;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Http\SaloonResponse;
use Throwable;

class SaloonMethodNotFoundException extends Exception
{
    public function __construct(string $method, SaloonConnector $connector)
    {
        parent::__construct(sprintf('Unable to find the method "%s" on either the request class or the "%s" connector.', $method, get_class($connector)));
    }
}
