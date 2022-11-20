<?php

declare(strict_types=1);

namespace Saloon\Exceptions;

class NoMockResponsesProvidedException extends SaloonException
{
    public function __construct()
    {
        parent::__construct('You are using the Saloon mock client but have not seeded it with any mock responses.');
    }
}
