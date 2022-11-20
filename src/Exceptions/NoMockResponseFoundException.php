<?php

declare(strict_types=1);

namespace Saloon\Exceptions;

class NoMockResponseFoundException extends SaloonException
{
    public function __construct()
    {
        parent::__construct('Saloon was unable to guess a mock response for your request, consider using a wildcard url mock or a connector mock.');
    }
}
