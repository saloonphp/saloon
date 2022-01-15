<?php

namespace Sammyjo20\Saloon\Http;

use Sammyjo20\Saloon\Traits\CollectsAuth;
use Sammyjo20\Saloon\Traits\CollectsConfig;
use Sammyjo20\Saloon\Traits\CollectsHeaders;
use Sammyjo20\Saloon\Traits\InterceptsRequests;
use Sammyjo20\Saloon\Interfaces\SaloonConnectorInterface;

abstract class SaloonConnector implements SaloonConnectorInterface
{
    use CollectsHeaders,
        CollectsAuth,
        CollectsConfig,
        InterceptsRequests;
}
