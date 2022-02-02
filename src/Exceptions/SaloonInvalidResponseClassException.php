<?php

namespace Sammyjo20\Saloon\Exceptions;

class SaloonInvalidResponseClassException extends SaloonException
{
    public function __construct()
    {
        parent::__construct('The provided response is not a valid. The class must also extend SaloonResponse.');
    }
}
