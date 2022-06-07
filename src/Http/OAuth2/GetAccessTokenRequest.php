<?php

namespace Sammyjo20\Saloon\Http\OAuth2;

use Carbon\CarbonImmutable;
use Sammyjo20\Saloon\Constants\Saloon;
use Sammyjo20\Saloon\Helpers\OAuth2Config;
use Sammyjo20\Saloon\Http\Auth\AccessTokenAuthenticator;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Http\SaloonResponse;
use Sammyjo20\Saloon\Traits\Plugins\AcceptsJson;
use Sammyjo20\Saloon\Traits\Plugins\CastsToDto;
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
     * Requires the authorization code and OAuth 2 config.
     *
     * @param string $code
     * @param OAuth2Config $oauthConfig
     */
    public function __construct(protected string $code, protected OAuth2Config $oauthConfig)
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
