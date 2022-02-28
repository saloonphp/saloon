<?php

namespace Sammyjo20\Saloon\Exceptions;

class MissingDtoCastException extends SaloonException
{
    /**
     *
     */
    public function __construct()
    {
        parent::__construct('Saloon was unable to cast to a DTO because a cast was not defined on your request. Add the "CastsToDto" plugin to your request to define a DTO to cast to.');
    }
}
