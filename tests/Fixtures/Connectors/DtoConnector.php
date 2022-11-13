<?php declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\SaloonConnector;
use Saloon\Traits\Plugins\CastsToDto;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Http\Responses\SaloonResponse;
use Saloon\Tests\Fixtures\Data\ApiResponse;

class DtoConnector extends SaloonConnector
{
    use AcceptsJson;
    use CastsToDto;

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
     * @param SaloonResponse $response
     * @return object
     */
    protected function castToDto(SaloonResponse $response): object
    {
        return ApiResponse::fromSaloon($response);
    }
}
