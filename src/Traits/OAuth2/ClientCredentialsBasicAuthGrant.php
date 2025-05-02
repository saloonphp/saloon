<?php

declare(strict_types=1);

namespace Saloon\Traits\OAuth2;

use Saloon\Http\Request;
use Saloon\Helpers\OAuth2\OAuthConfig;
use Saloon\Http\OAuth2\GetClientCredentialsTokenBasicAuthRequest;

/**
 * @phpstan-ignore trait.unused
 */
trait ClientCredentialsBasicAuthGrant
{
    use ClientCredentialsGrant;

    /**
     * Resolve the access token request
     */
    protected function resolveAccessTokenRequest(OAuthConfig $oauthConfig, array $scopes = [], string $scopeSeparator = ' '): Request
    {
        return new GetClientCredentialsTokenBasicAuthRequest($oauthConfig, $scopes, $scopeSeparator);
    }
}
