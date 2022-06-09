<?php

namespace Sammyjo20\Saloon\Http\OAuth2;

use Sammyjo20\Saloon\Constants\Saloon;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Helpers\OAuth2\OAuthConfig;
use Sammyjo20\Saloon\Traits\Plugins\AcceptsJson;
use Sammyjo20\Saloon\Traits\Plugins\HasFormParams;

class GetUserRequest extends SaloonRequest
{
    use HasFormParams;
    use AcceptsJson;

    /**
     * Define the method that the request will use.
     *
     * @var string|null
     */
    protected ?string $method = Saloon::GET;

    /**
     * Define the endpoint for the request.
     *
     * @return string
     */
    public function defineEndpoint(): string
    {
        return $this->oauthConfig->getUserEndpoint();
    }

    /**
     * Requires the authorization code and OAuth 2 config.
     *
     * @param OAuthConfig $oauthConfig
     */
    public function __construct(protected OAuthConfig $oauthConfig)
    {
        //
    }
}
