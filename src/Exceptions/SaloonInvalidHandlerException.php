<?php

namespace Sammyjo20\Saloon\Exceptions;

class SaloonInvalidHandlerException extends SaloonException
{
    public function __construct(string $name)
    {
        parent::__construct(sprintf('The "%s" handler must return a middleware callable.', $name));
    }
}
