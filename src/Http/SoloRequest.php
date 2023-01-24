<?php

declare(strict_types=1);

namespace Saloon\Http;

use Saloon\Contracts\Connector;
use Saloon\Traits\Request\HasConnector;
use Saloon\Http\Connectors\NullConnector;

abstract class SoloRequest extends Request
{
    use HasConnector;

    /**
     * Create a new connector instance.
     *
     * @return \Saloon\Contracts\Connector
     */
    protected function resolveConnector(): Connector
    {
        return new NullConnector;
    }
}
