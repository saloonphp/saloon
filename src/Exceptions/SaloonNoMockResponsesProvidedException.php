<?php

namespace Sammyjo20\Saloon\Exceptions;

use \Exception;

class SaloonNoMockResponsesProvidedException extends Exception
{
    public function __construct()
    {
        parent::__construct('You are using the Saloon mock client but have not seeded it with any mock responses.');
    }
}
