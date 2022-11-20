<?php

declare(strict_types=1);

namespace Saloon\Exceptions;

class InvalidMockResponseCaptureMethodException extends SaloonException
{
    public function __construct()
    {
        parent::__construct('The provided capture method is invalid. It must be a string of a request/connector class or a url.');
    }
}
