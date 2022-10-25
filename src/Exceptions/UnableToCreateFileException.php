<?php

namespace Sammyjo20\Saloon\Exceptions;

class UnableToCreateFileException extends SaloonException
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
