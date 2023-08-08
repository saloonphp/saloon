<?php

declare(strict_types=1);

namespace Saloon\Exceptions;

class UnableToCreateFileException extends SaloonException
{
    /**
     * Constructor
     */
    public function __construct(string $path)
    {
        parent::__construct(sprintf('We were unable to create the "%s" file.', $path));
    }
}
