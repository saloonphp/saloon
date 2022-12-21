<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\Connector;
use Saloon\Contracts\Body\HasBody;
use Saloon\Traits\Body\HasJsonBody;
use Saloon\Traits\Plugins\AcceptsJson;

class PostJsonConnector extends Connector implements HasBody
{
    use AcceptsJson;
    use HasJsonBody;

    public function resolveBaseUrl(): string
    {
        return apiUrl();
    }

    public function defaultBody(): array
    {
        return [
            'connectorId' => 1,
        ];
    }
}
