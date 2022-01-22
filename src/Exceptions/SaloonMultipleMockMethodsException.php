<?php

namespace Sammyjo20\Saloon\Exceptions;

use \Exception;

class SaloonMultipleMockMethodsException extends Exception
{
    public function __construct()
    {
        parent::__construct('You are using both the Laravel Mock and the MockClient. Saloon only supports one mock methods at a time.');
    }
}
