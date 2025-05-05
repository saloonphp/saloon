<?php

declare(strict_types=1);

namespace Saloon\Exceptions;

use Saloon\Http\Response;

class InvalidResponseClassException extends SaloonException
{
    /**
     * Constructor
     */
    public function __construct(?string $message = null)
    {
        parent::__construct($message ?? sprintf('The provided response must exist and implement the %s contract.', Response::class));
    }
}
