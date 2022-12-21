<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\Connector;
use Saloon\Tests\Fixtures\Requests\InvalidResponseClass;

class InvalidResponseConnector extends Connector
{
    protected ?string $response = InvalidResponseClass::class;

    /**
     * Define the base url of the api.
     *
     * @return string
     */
    public function resolveBaseUrl(): string
    {
        return apiUrl();
    }
}
