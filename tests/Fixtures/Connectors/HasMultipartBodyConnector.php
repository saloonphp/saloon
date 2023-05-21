<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Contracts\Body\HasBody;
use Saloon\Data\MultipartValue;
use Saloon\Http\Connector;
use Saloon\Traits\Body\HasJsonBody;
use Saloon\Traits\Body\HasMultipartBody;
use Saloon\Traits\Plugins\AcceptsJson;

class HasMultipartBodyConnector extends Connector implements HasBody
{
    use AcceptsJson;
    use HasMultipartBody;

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
            new MultipartValue('nickname', 'Gareth', 'user.txt', ['X-Saloon' => 'Yee-haw!']),
            new MultipartValue('drink', 'Moonshine', 'moonshine.txt', ['X-My-Head' => 'Spinning!']),
        ];
    }
}
