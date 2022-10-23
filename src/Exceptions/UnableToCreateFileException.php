<?php

namespace Sammyjo20\Saloon\Exceptions;

use Exception;

class UnableToCreateFileException extends Exception
{
    /**
     * Constructor
     *
     * @param string $path
     */
    public function __construct(string $path)
    {
        parent::__construct(sprintf('We were unable to create the "%s" file.', $path));
    }
}
