<?php

namespace Sammyjo20\Saloon\Tests\Fixtures\Connectors;

use Sammyjo20\Saloon\Http\SaloonConnector;
use Sammyjo20\Saloon\Tests\Fixtures\Responses\CustomResponse;

class CustomResponseConnector extends SaloonConnector
{
    protected string $response = CustomResponse::class;

    /**
     * Define the base url of the api.
     *
     * @return string
     */
    public function defineBaseUrl(): string
    {
        return apiUrl();
    }
}
