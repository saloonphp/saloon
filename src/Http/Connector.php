<?php

declare(strict_types=1);

namespace Saloon\Http;

use Saloon\Traits\Bootable;
use Saloon\Traits\Makeable;
use Saloon\Traits\HasDebugging;
use Saloon\Traits\Conditionable;
use Saloon\Traits\HasMockClient;
use Saloon\Traits\Connector\HasPool;
use Saloon\Traits\HandlesExceptions;
use Saloon\Traits\Connector\HasSender;
use Saloon\Traits\Connector\SendsRequests;
use Saloon\Traits\Auth\AuthenticatesRequests;
use Saloon\Traits\RequestProperties\HasDelay;
use Saloon\Traits\Request\CastDtoFromResponse;
use Saloon\Traits\Responses\HasCustomResponses;
use Saloon\Contracts\Connector as ConnectorContract;
use Saloon\Traits\RequestProperties\HasRequestProperties;
use Saloon\Contracts\HasDebugging as HasDebuggingContract;

abstract class Connector implements ConnectorContract, HasDebuggingContract
{
    use AuthenticatesRequests;
    use HasRequestProperties;
    use CastDtoFromResponse;
    use HasCustomResponses;
    use HandlesExceptions;
    use HasMockClient;
    use SendsRequests;
    use Conditionable;
    use HasSender;
    use Bootable;
    use Makeable;
    use HasPool;
    use HasDelay;
    use HasDebugging;
}
