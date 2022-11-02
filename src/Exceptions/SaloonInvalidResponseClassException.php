<?php declare(strict_types=1);

namespace Sammyjo20\Saloon\Exceptions;

class SaloonInvalidResponseClassException extends SaloonException
{
    public function __construct(string $message = null)
    {
        parent::__construct($message ?? 'The provided response is not a valid. The class must also extend SaloonResponse.');
    }
}
