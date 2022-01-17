<?php

namespace Sammyjo20\Saloon\Exceptions;

use \Exception;

class SaloonDuplicateHandlerException extends Exception
{
    public function __construct(string $name)
    {
        parent::__construct(sprintf('The "%s" handler must be registered twice. Please check it is not on your connector.', $name));
    }
}
