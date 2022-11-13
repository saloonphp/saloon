<?php declare(strict_types=1);

namespace Saloon\Exceptions;

use Saloon\Http\Connector;

class NestedRequestNotFoundException extends SaloonException
{
    public function __construct(string $method, string $collectionName, Connector $connector)
    {
        parent::__construct(sprintf('Unable to find the "%s" request method in the "%s" collection on the "%s" connector.', $method, $collectionName, get_class($connector)));
    }
}
