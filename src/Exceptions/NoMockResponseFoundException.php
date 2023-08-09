<?php

declare(strict_types=1);

namespace Saloon\Exceptions;

use Saloon\Contracts\Request;

class NoMockResponseFoundException extends SaloonException
{
    public function __construct(Request $request)
    {
        parent::__construct("Saloon was unable to guess a mock response for your request [{$request->resolveEndpoint()}], consider using a wildcard url mock or a connector mock.");
    }
}
