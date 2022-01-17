<?php

namespace Sammyjo20\Saloon\Exceptions;

use \Exception;

class SaloonInvalidHandlerException extends Exception
{
    public function __construct(string $name)
    {
        parent::__construct(sprintf('The "%s" handler must return a middleware callable.', $name));
    }
}
