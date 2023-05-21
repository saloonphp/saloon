<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Contracts\Body\HasBody;
use Saloon\Http\Connector;
use Saloon\Traits\Body\HasJsonBody;
use Saloon\Traits\Plugins\AcceptsJson;

class HasJsonBodyConnector extends Connector implements HasBody
{
    use AcceptsJson;
    use HasJsonBody;

    public bool $unique = false;

    /**
     * Constructor
     *
     * @param string|null $url
     */
    public function __construct(protected ?string $url = null)
    {
        //
    }

    /**
     * Define the base url of the api.
     *
     * @return string
     */
    public function resolveBaseUrl(): string
    {
        return $this->url ?? apiUrl();
    }

    /**
     * Define the base headers that will be applied in every request.
     *
     * @return string[]
     */
    protected function defaultHeaders(): array
    {
        return [
            'Accept' => 'application/json',
        ];
    }

    protected function defaultBody(): array
    {
        return [
            'name' => 'Gareth',
            'drink' => 'Moonshine'
        ];
    }
}
