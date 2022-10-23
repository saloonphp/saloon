<?php

namespace Sammyjo20\Saloon\Exceptions;

use Exception;

class UnableToCreateDirectoryException extends Exception
{
    /**
     * Constructor
     *
     * @param string $directory
     */
    public function __construct(string $directory)
    {
        parent::__construct(sprintf('Unable to create the directory: %s.', $directory));
    }
}
