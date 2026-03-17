<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Http\Connector;
use Saloon\Helpers\OAuth2\OAuthConfig;
use Saloon\Traits\OAuth2\AuthorizationCodeGrant;

class AuthorizeUrlOAuthConnector extends Connector
{
    use AuthorizationCodeGrant;

    public function resolveBaseUrl(): string
    {
        return 'https://api.app.com';
    }

    protected function defaultOauthConfig(): OAuthConfig
    {
        $config = OAuthConfig::make()
            ->setClientId('id')
            ->setClientSecret('secret')
            ->setRedirectUri('https://app.com/cb')
            ->setAuthorizeEndpoint('https://login.provider.com/authorize');
        $config->setAllowBaseUrlOverride();

        return $config;
    }
}
