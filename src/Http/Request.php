<?php

declare(strict_types=1);

namespace Saloon\Http;

use Saloon\Contracts\Request as RequestContract;
use Saloon\Enums\Method;
use Saloon\Traits\Auth\AuthenticatesRequests;
use Saloon\Traits\Bootable;
use Saloon\Traits\Conditionable;
use Saloon\Traits\HandlesPsrRequest;
use Saloon\Traits\HasDebugging;
use Saloon\Traits\HasMockClient;
use Saloon\Traits\Makeable;
use Saloon\Traits\ManagesExceptions;
use Saloon\Traits\Request\CreatesDtoFromResponse;
use Saloon\Traits\RequestProperties\HasRequestProperties;
use Saloon\Traits\RequestProperties\HasTries;
use Saloon\Traits\Responses\HasCustomResponses;

abstract class Request implements RequestContract
{
    use AuthenticatesRequests;
    use HasRequestProperties;
    use CreatesDtoFromResponse;
    use HasCustomResponses;
    use ManagesExceptions;
    use HandlesPsrRequest;
    use HasMockClient;
    use Conditionable;
    use HasDebugging;
    use Bootable;
    use Makeable;
    use HasTries;

    /**
     * Define the HTTP method.
     */
    protected Method $method;

    /**
     * Get the method of the request.
     */
    public function getMethod(): Method
    {
        return $this->method;
    }
}
