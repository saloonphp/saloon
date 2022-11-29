<?php

declare(strict_types=1);

namespace Saloon\Tests\Integration;

use Saloon\Http\Connector;
use Saloon\Contracts\Response;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Traits\Plugins\AlwaysThrowsOnErrors;

class SteamConnector extends Connector
{
    use AcceptsJson;
    use AlwaysThrowsOnErrors;

    public function defineBaseUrl(): string
    {
        return 'https://api.steampowered.com';
    }

    public function defaultQuery(): array
    {
        return array_filter([
            'format' => 'json',
        ]);
    }

    public function getAppList(): Response
    {
        $request = new GetAppListRequest();

        return $this->send($request);
    }
}
