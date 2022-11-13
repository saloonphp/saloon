<?php declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\SaloonConnector;
use Saloon\Tests\Fixtures\Requests\InvalidResponseClass;

class InvalidResponseConnector extends SaloonConnector
{
    protected ?string $response = InvalidResponseClass::class;

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
