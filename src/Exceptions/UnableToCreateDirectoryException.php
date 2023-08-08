<?php

declare(strict_types=1);

namespace Saloon\Exceptions;

class UnableToCreateDirectoryException extends SaloonException
{
    /**
     * Constructor
     */
    public function __construct(string $directory)
    {
        parent::__construct(sprintf('Unable to create the directory: %s.', $directory));
    }
}
