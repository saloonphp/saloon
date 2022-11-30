<?php

declare(strict_types=1);

namespace Saloon\Traits\Responses;

use ReflectionException;
use Saloon\Contracts\Request;
use Saloon\Helpers\ReflectionHelper;
use Saloon\Exceptions\InvalidConnectorException;
use Saloon\Exceptions\InvalidResponseClassException;

trait HasCustomResponses
{
    /**
     * Specify a default response.
     *
     * When an empty string, the response on the sender will be used.
     *
     * @var string
     */
    protected string $response = '';

    /**
     * Resolve the custom response class
     *
     * @return string
     */
    public function resolveResponseClass(): string
    {
        return $this->response;
    }
}
