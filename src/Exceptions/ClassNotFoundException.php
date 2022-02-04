<?php

namespace Sammyjo20\Saloon\Exceptions;

class ClassNotFoundException extends SaloonException
{
    /**
     * @param string $request
     */
    public function __construct(string $request)
    {
        parent::__construct(sprintf('The provided class "%s" could not be found.', $request));
    }
}
