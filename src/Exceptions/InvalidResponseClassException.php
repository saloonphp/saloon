<?php

declare(strict_types=1);

namespace Saloon\Exceptions;

use Saloon\Contracts\Response;

class InvalidResponseClassException extends SaloonException
{
    /**
     * Constructor
     *
     * @param string|null $message
     */
    public function __construct(string $message = null)
    {
        parent::__construct($message ?? sprintf('The provided response must exist and implement the %s contract.', Response::class));
    }
}
