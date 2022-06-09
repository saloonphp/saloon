<?php

namespace Sammyjo20\Saloon\Exceptions;

use Throwable;

class InvalidStateException extends SaloonException
{
    public function __construct(string $message = null, int $code = 0, ?Throwable $previous = null)
    {
        parent::__construct($message ?? 'Invalid state.', $code, $previous);
    }
}
