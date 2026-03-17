<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\Connector;
use Saloon\Helpers\OAuth2\OAuthConfig;
use Saloon\Traits\OAuth2\ClientCredentialsGrant;

class AbsoluteTokenOAuthConnector extends Connector
{
    use ClientCredentialsGrant;

    public function resolveBaseUrl(): string
    {
        return 'https://api.service.com';
    }

    protected function defaultOauthConfig(): OAuthConfig
    {
        $config = OAuthConfig::make()
            ->setClientId('id')
            ->setClientSecret('secret')
            ->setTokenEndpoint('https://auth.external.com/oauth/token');
        $config->setAllowBaseUrlOverride();

        return $config;
    }
}
