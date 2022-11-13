<?php declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\SaloonRequest;
use Saloon\Http\SaloonConnector;
use Saloon\Traits\Plugins\AcceptsJson;

class WithBootConnector extends SaloonConnector
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

    public function boot(SaloonRequest $request): void
    {
        $this->addHeader('X-Connector-Boot-Header', 'Howdy!');
        $this->addHeader('X-Connector-Request-Class', get_class($request));
    }
}
