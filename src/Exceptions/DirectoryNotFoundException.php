<?php

namespace Sammyjo20\Saloon\Exceptions;

class DirectoryNotFoundException extends SaloonException
{
    /**
     * Constructor
     *
     * @param string $directory
     */
    public function __construct(string $directory)
    {
        parent::__construct(sprintf('The directory "%s" does not exist or is not a valid directory.', $directory));
    }
}
