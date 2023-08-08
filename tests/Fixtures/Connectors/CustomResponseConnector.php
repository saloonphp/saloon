<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\Connector;
use Saloon\Tests\Fixtures\Responses\CustomResponse;

class CustomResponseConnector extends Connector
{
    protected ?string $response = CustomResponse::class;

    /**
     * Define the base url of the api.
     */
    public function resolveBaseUrl(): string
    {
        return apiUrl();
    }
}
