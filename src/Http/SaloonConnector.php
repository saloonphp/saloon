<?php

namespace Sammyjo20\Saloon\Http;

use Sammyjo20\Saloon\Traits\CollectsData;
use Sammyjo20\Saloon\Traits\CollectsConfig;
use Sammyjo20\Saloon\Traits\CollectsHeaders;
use Sammyjo20\Saloon\Traits\CollectsHandlers;
use Sammyjo20\Saloon\Traits\CollectsQueryParams;
use Sammyjo20\Saloon\Traits\CollectsInterceptors;
use Sammyjo20\Saloon\Interfaces\SaloonConnectorInterface;

abstract class SaloonConnector implements SaloonConnectorInterface
{
    use CollectsHeaders,
        CollectsData,
        CollectsQueryParams,
        CollectsConfig,
        CollectsHandlers,
        CollectsInterceptors;

    /**
     * The response class.
     *
     * @var class-string<\Sammyjo20\Saloon\Http\SaloonResponse>|null
     */
    protected ?string $response = null;

    /**
     * Gets the response class.
     * @return string|null
     */
    public function getResponseClass(): ?string {
        return $this->response;
    }

    /**
     * Define anything to be added to the connector.
     *
     * @return void
     */
    public function boot(): void
    {
        // TODO: Implement boot() method.
    }
}
