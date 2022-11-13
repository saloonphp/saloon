<?php declare(strict_types=1);

namespace Saloon\Exceptions;

class SaloonInvalidResponseClassException extends SaloonException
{
    /**
     * Constructor
     *
     * @param string|null $message
     */
    public function __construct(string $message = null)
    {
        parent::__construct($message ?? 'The provided response must implement the Response contract.');
    }
}
