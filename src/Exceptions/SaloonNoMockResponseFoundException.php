<?php

namespace Sammyjo20\Saloon\Exceptions;

class SaloonNoMockResponseFoundException extends SaloonException
{
    public function __construct()
    {
        parent::__construct('Saloon was unable to guess a mock response for your request, consider using a wildcard url mock or a connector mock.');
    }
}
