<?php

namespace Sammyjo20\Saloon\Exceptions;

class SaloonMultipleMockMethodsException extends SaloonException
{
    public function __construct()
    {
        parent::__construct('You are using both the Laravel Mock and the MockClient. Saloon only supports one mock methods at a time.');
    }
}
