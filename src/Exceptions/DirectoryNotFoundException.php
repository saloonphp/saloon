<?php

declare(strict_types=1);

namespace Saloon\Exceptions;

class DirectoryNotFoundException extends SaloonException
{
    /**
     * Constructor
     */
    public function __construct(string $directory)
    {
        parent::__construct(sprintf('The directory "%s" does not exist or is not a valid directory.', $directory));
    }
}
