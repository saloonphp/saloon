<?php

namespace Sammyjo20\Saloon\Http;

use Sammyjo20\Saloon\Traits\CollectsAuth;
use Sammyjo20\Saloon\Traits\CollectsConfig;
use Sammyjo20\Saloon\Traits\CollectsData;
use Sammyjo20\Saloon\Traits\CollectsHeaders;
use Sammyjo20\Saloon\Traits\InterceptsRequests;
use Sammyjo20\Saloon\Interfaces\SaloonConnectorInterface;

abstract class SaloonConnector implements SaloonConnectorInterface
{
    use CollectsHeaders,
        CollectsData,
        CollectsAuth,
        CollectsConfig,
        InterceptsRequests;

    /**
     * Default post data
     *
     * @return array
     */
    public function postData(): array
    {
        return [];
    }
}
