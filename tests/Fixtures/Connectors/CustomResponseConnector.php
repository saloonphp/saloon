<?php declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\SaloonConnector;
use Saloon\Tests\Fixtures\Responses\CustomResponse;

class CustomResponseConnector extends SaloonConnector
{
    protected ?string $response = CustomResponse::class;

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
