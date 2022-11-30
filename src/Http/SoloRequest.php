<?php

namespace Saloon\Http;

use Saloon\Contracts\Connector;
use Saloon\Http\Connectors\NullConnector;
use Saloon\Traits\Request\HasConnector;

abstract class SoloRequest extends Request
{
    use HasConnector;

    /**
     * Create a new connector instance.
     *
     * @return Connector
     */
    protected function resolveConnector(): Connector
    {
        return new NullConnector;
    }
}
