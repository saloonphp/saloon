<?php

namespace Sammyjo20\Saloon\Exceptions;

use \Exception;

class SaloonInvalidMockResponseCaptureMethodException extends Exception
{
    public function __construct()
    {
        parent::__construct('The provided capture method is invalid. It must be a string of a request/connector class or a url.');
    }
}
