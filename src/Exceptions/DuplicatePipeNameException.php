<?php

declare(strict_types=1);

namespace Saloon\Exceptions;

class DuplicatePipeNameException extends SaloonException
{
    /**
     * Constructor
     */
    public function __construct(string $name)
    {
        parent::__construct(sprintf('The "%s" pipe already exists on the pipeline', $name));
    }
}
