<?php declare(strict_types=1);

namespace Saloon\Exceptions;

use Saloon\Http\Connector;

class ConnectorMethodNotFoundException extends SaloonException
{
    public function __construct(string $method, Connector $connector)
    {
        parent::__construct(sprintf('Unable to find the "%s" method on the "%s" connector.', $method, get_class($connector)));
    }
}
