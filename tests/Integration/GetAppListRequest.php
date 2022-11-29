<?php

declare(strict_types=1);

namespace Saloon\Tests\Integration;

use Saloon\Http\Request;
use Saloon\Contracts\Response;
use Illuminate\Support\Collection;
use Saloon\Traits\Request\CastDtoFromResponse;

class GetAppListRequest extends Request
{
    use CastDtoFromResponse;

    protected string $method = 'GET';

    public string $foobar = 'baz';

    public function defineEndpoint(): string
    {
        return '/ISteamApps/GetAppList/v2';
    }

    /**
     * @param \Saloon\Contracts\Response $response
     * @return \Illuminate\Support\Collection<array-key, \Saloon\Tests\Integration\App>
     */
    public function createDtoFromResponse(Response $response): Collection
    {
        return $response->collect('applist.apps')
            ->map(fn (array $app) => new App(
                appid: $app['appid'],
                name: $app['name'] ?? null
            ))
            ->values();
    }
}
