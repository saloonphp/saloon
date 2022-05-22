<?php

namespace Sammyjo20\Saloon\Exceptions;

use Sammyjo20\Saloon\Http\SaloonConnector;

class NestedRequestNotFoundException extends SaloonException
{
    public function __construct(string $method, string $collectionName, SaloonConnector $connector)
    {
        parent::__construct(sprintf('Unable to find the "%s" request method in the "%s" collection on the "%s" connector.', $method, $collectionName, get_class($connector)));
    }
}
