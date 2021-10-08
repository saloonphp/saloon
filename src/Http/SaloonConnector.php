<?php

namespace Sammyjo20\Saloon\Http;

use Sammyjo20\Saloon\Interfaces\SaloonConnectorInterface;
use Sammyjo20\Saloon\Traits\CollectsAuth;
use Sammyjo20\Saloon\Traits\CollectsConfig;
use Sammyjo20\Saloon\Traits\CollectsHeaders;
use Sammyjo20\Saloon\Traits\CollectsQuery;
use Sammyjo20\Saloon\Traits\InterceptsGuzzle;
use Sammyjo20\Saloon\Traits\SendsRequests;

abstract class SaloonConnector implements SaloonConnectorInterface
{
    use CollectsHeaders,
        CollectsAuth,
        CollectsQuery, // Todo: Do we really need to have a collector for this?
        CollectsConfig;

    use SendsRequests;
    use InterceptsGuzzle;

    public function __construct()
    {
        $this->setHeaders($this->defineHeaders());
        $this->setAuth($this->defineAuth());
        $this->setQuery($this->defineQuery());
        $this->setConfig($this->defineConfig());
    }
}
