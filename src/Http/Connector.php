<?php

declare(strict_types=1);

namespace Saloon\Http;

use Saloon\Traits\Bootable;
use Saloon\Traits\Makeable;
use Saloon\Traits\Macroable;
use Saloon\Traits\HasDebugging;
use Saloon\Traits\Conditionable;
use Saloon\Traits\HasMockClient;
use Saloon\Traits\HandlesPsrRequest;
use Saloon\Traits\ManagesExceptions;
use Saloon\Traits\Connector\SendsRequests;
use Saloon\Traits\Auth\AuthenticatesRequests;
use Saloon\Traits\RequestProperties\HasTries;
use Saloon\Traits\Responses\HasCustomResponses;
use Saloon\Traits\Request\CreatesDtoFromResponse;
use Saloon\Traits\RequestProperties\HasRequestProperties;

abstract class Connector
{
    use CreatesDtoFromResponse;
    use AuthenticatesRequests;
    use HasRequestProperties;
    use HasCustomResponses;
    use ManagesExceptions;
    use HandlesPsrRequest;
    use HasMockClient;
    use SendsRequests;
    use Conditionable;
    use HasDebugging;
    use Macroable;
    use Bootable;
    use Makeable;
    use HasTries;

    /**
     * When true, resolveEndpoint() may return an absolute URL (different host than base).
     * Set on the connector instance or declare e.g. `public bool $allowBaseUrlOverride = true` on your subclass.
     * Enabling with user-controlled endpoints risks SSRF and credential leakage.
     */
    public bool $allowBaseUrlOverride = false;

    /**
     * Define the base URL of the API.
     */
    abstract public function resolveBaseUrl(): string;
}
