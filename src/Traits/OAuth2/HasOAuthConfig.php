<?php

declare(strict_types=1);

namespace Saloon\Traits\OAuth2;

use Saloon\Helpers\OAuth2\OAuthConfig;

trait HasOAuthConfig
{
    /**
     * The OAuth2 Config
     */
    protected OAuthConfig $oauthConfig;

    /**
     * Manage the OAuth2 config
     */
    public function oauthConfig(): OAuthConfig
    {
        return $this->oauthConfig ??= $this->defaultOauthConfig();
    }

    /**
     * Define the default Oauth 2 Config.
     */
    protected function defaultOauthConfig(): OAuthConfig
    {
        return OAuthConfig::make();
    }
}
