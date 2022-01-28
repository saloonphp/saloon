<?php

namespace Sammyjo20\Saloon\Exceptions;

use Exception;

class SaloonInvalidResponseClassException extends Exception
{
    public function __construct()
    {
        parent::__construct('The provided response is not a valid. The class must also extend SaloonResponse.');
    }
}
