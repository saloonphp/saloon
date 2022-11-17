<?php declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\Connector;
use Saloon\Contracts\Response;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Tests\Fixtures\Data\ApiResponse;

class DtoConnector extends Connector
{
    use AcceptsJson;

    /**
     * Define the base url of the api.
     *
     * @return string
     */
    public function defineBaseUrl(): string
    {
        return apiUrl();
    }

    /**
     * Define the base headers that will be applied in every request.
     *
     * @return string[]
     */
    public function defaultHeaders(): array
    {
        return [];
    }

    /**
     * Create DTO from Response
     *
     * @param Response $response
     * @return mixed
     */
    public function createDtoFromResponse(Response $response): mixed
    {
        return ApiResponse::fromSaloon($response);
    }
}
