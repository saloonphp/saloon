<?php

namespace Sammyjo20\Saloon\Exceptions;

class UnableToCreateDirectoryException extends SaloonException
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
