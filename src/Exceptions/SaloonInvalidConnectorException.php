<?php

namespace Sammyjo20\Saloon\Exceptions;

use \Exception;

class SaloonInvalidConnectorException extends Exception
{
    public function __construct()
    {
        parent::__construct('The provided connector is not a valid. The class must also extend SaloonConnector.');
    }
}
