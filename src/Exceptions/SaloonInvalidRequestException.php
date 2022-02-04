<?php

namespace Sammyjo20\Saloon\Exceptions;

class SaloonInvalidRequestException extends SaloonException
{
    /**
     * @param string $request
     */
    public function __construct(string $request)
    {
        parent::__construct(sprintf('The provided request "%s" class is not a valid SaloonRequest.', $request));
    }
}
