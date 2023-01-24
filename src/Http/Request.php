<?php

declare(strict_types=1);

namespace Saloon\Http;

use Saloon\Enums\Method;
use Saloon\Traits\Bootable;
use Saloon\Traits\Makeable;
use Saloon\Traits\Conditionable;
use Saloon\Traits\HasMockClient;
use Saloon\Traits\HandlesExceptions;
use Saloon\Traits\Auth\AuthenticatesRequests;
use Saloon\Traits\Request\CastDtoFromResponse;
use Saloon\Traits\Responses\HasCustomResponses;
use Saloon\Contracts\Request as RequestContract;
use Saloon\Traits\RequestProperties\HasRequestProperties;

abstract class Request implements RequestContract
{
    use AuthenticatesRequests;
    use HasRequestProperties;
    use CastDtoFromResponse;
    use HasCustomResponses;
    use HandlesExceptions;
    use HasMockClient;
    use Conditionable;
    use Bootable;
    use Makeable;

    /**
     * Define the HTTP method.
     *
     * @var \Saloon\Enums\Method
     */
    protected Method $method;

    /**
     * Get the method of the request.
     *
     * @return \Saloon\Enums\Method
     */
    public function getMethod(): Method
    {
        return $this->method;
    }
}
