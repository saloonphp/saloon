<?php declare(strict_types=1);

namespace Saloon\Exceptions;

class InvalidConnectorException extends SaloonException
{
    public function __construct()
    {
        parent::__construct('The connector defined is not a valid. The class must also extend Connector.');
    }
}
