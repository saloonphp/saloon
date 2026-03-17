<?php

declare(strict_types=1);

namespace Saloon\Http;

use Saloon\Traits\Request\HasConnector;
use Saloon\Http\Connectors\NullConnector;

abstract class SoloRequest extends Request
{
    use HasConnector;

    /**
     * When true, OAuth endpoints (authorize, token, user) may be full URLs that differ from the connector base.
     * Do not enable with user-controlled endpoint values (SSRF / credential leakage).
     */
    public ?bool $allowBaseUrlOverride = true;

    /**
     * Create a new connector instance.
     */
    protected function resolveConnector(): Connector
    {
        return new NullConnector;
    }
}
