<?php

namespace Sammyjo20\Saloon\Exceptions;

use \Exception;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Http\SaloonResponse;
use Throwable;

class SaloonInvalidConnectorException extends Exception
{
    public function __construct()
    {
        parent::__construct('The provided connector is invalid.');
    }
}
