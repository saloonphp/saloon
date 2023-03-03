<?php

declare(strict_types=1);

namespace Saloon\Traits\OAuth2;

use Saloon\Helpers\OAuth2\OAuthConfig;

trait HasOAuthConfig
{
    /**
     * The OAuth2 Config
     *
     * @var \Saloon\Helpers\OAuth2\OAuthConfig
     */
    protected OAuthConfig $oauthConfig;

    /**
     * Manage the OAuth2 config
     *
     * @return \Saloon\Helpers\OAuth2\OAuthConfig
     */
    public function oauthConfig(): OAuthConfig
    {
        return $this->oauthConfig ??= $this->defaultOauthConfig();
    }

    /**
     * Define the default Oauth 2 Config.
     *
     * @return \Saloon\Helpers\OAuth2\OAuthConfig
     */
    protected function defaultOauthConfig(): OAuthConfig
    {
        return OAuthConfig::make();
    }
}
