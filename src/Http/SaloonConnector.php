<?php

namespace Sammyjo20\Saloon\Http;

use Sammyjo20\Saloon\Interfaces\SaloonConnectorInterface;
use Sammyjo20\Saloon\Traits\CollectsAuth;
use Sammyjo20\Saloon\Traits\CollectsConfig;
use Sammyjo20\Saloon\Traits\CollectsHeaders;
use Sammyjo20\Saloon\Traits\InterceptsGuzzle;

abstract class SaloonConnector implements SaloonConnectorInterface
{
    use CollectsHeaders,
        CollectsAuth,
        CollectsConfig;

    use InterceptsGuzzle;

    public function __construct()
    {
        $this->setAuth($this->defineAuth());
    }
}
