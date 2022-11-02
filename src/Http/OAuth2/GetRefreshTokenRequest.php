<?php

namespace Sammyjo20\Saloon\Http\OAuth2;

use Sammyjo20\Saloon\Contracts\Body\WithBody;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Helpers\OAuth2\OAuthConfig;
use Sammyjo20\Saloon\Traits\Body\HasFormBody;
use Sammyjo20\Saloon\Traits\Plugins\AcceptsJson;

class GetRefreshTokenRequest extends SaloonRequest implements WithBody
{
    use HasFormBody;
    use AcceptsJson;

    /**
     * Define the method that the request will use.
     *
     * @var string
     */
    protected string $method = 'POST';

    /**
     * Define the endpoint for the request.
     *
     * @return string
     */
    public function defineEndpoint(): string
    {
        return $this->oauthConfig->getTokenEndpoint();
    }

    /**
     * Requires the authorization code and OAuth 2 config.
     *
     * @param OAuthConfig $oauthConfig
     * @param string $refreshToken
     */
    public function __construct(protected OAuthConfig $oauthConfig, protected string $refreshToken)
    {
        //
    }

    /**
     * Register the default data.
     *
     * @return array
     */
    public function defaultBody(): array
    {
        return [
            'grant_type' => 'refresh_token',
            'refresh_token' => $this->refreshToken,
            'client_id' => $this->oauthConfig->getClientId(),
            'client_secret' => $this->oauthConfig->getClientSecret(),
        ];
    }
}
