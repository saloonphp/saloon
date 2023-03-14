<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\Connector;
use Saloon\Helpers\OAuth2\OAuthConfig;
use Saloon\Traits\OAuth2\ClientCredentialsGrant;

class ClientCredentialsConnector extends Connector
{
    use ClientCredentialsGrant;

    /**
     * Define the base URL.
     *
     * @return string
     */
    public function resolveBaseUrl(): string
    {
        return 'https://oauth.saloon.dev';
    }

    /**
     * Define default Oauth config.
     *
     * @return OAuthConfig
     */
    protected function defaultOauthConfig(): OAuthConfig
    {
        return OAuthConfig::make()
            ->setClientId('client-id')
            ->setClientSecret('client-secret');
    }
}
