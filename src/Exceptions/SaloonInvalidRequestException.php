<?php declare(strict_types=1);

namespace Saloon\Exceptions;

class SaloonInvalidRequestException extends SaloonException
{
    /**
     * @param string $request
     */
    public function __construct(string $request)
    {
        parent::__construct(sprintf('The provided request "%s" class is not a valid Request.', $request));
    }
}
