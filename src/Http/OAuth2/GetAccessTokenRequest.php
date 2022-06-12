<?php

namespace Sammyjo20\Saloon\Http\OAuth2;

use Sammyjo20\Saloon\Constants\Saloon;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Helpers\OAuth2\OAuthConfig;
use Sammyjo20\Saloon\Traits\Plugins\AcceptsJson;
use Sammyjo20\Saloon\Traits\Plugins\HasFormParams;

class GetAccessTokenRequest extends SaloonRequest
{
    use HasFormParams;
    use AcceptsJson;

    /**
     * Define the method that the request will use.
     *
     * @var string|null
     */
    protected ?string $method = Saloon::POST;

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
     * @param string $code
     * @param OAuthConfig $oauthConfig
     */
    public function __construct(protected string $code, protected OAuthConfig $oauthConfig)
    {
        //
    }

    /**
     * Register the default data.
     *
     * @return array
     */
    public function defaultData(): array
    {
        return [
            'grant_type' => 'authorization_code',
            'code' => $this->code,
            'client_id' => $this->oauthConfig->getClientId(),
            'client_secret' => $this->oauthConfig->getClientSecret(),
            'redirect_uri' => $this->oauthConfig->getRedirectUri(),
        ];
    }
}
