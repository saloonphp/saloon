<?php

declare(strict_types=1);

namespace Saloon\Http\OAuth2;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Contracts\Body\HasBody;
use Saloon\Contracts\Authenticator;
use Saloon\Traits\Body\HasFormBody;
use Saloon\Helpers\OAuth2\OAuthConfig;
use Saloon\Traits\Plugins\AcceptsJson;
use Saloon\Http\Auth\BasicAuthenticator;

class GetClientCredentialsTokenBasicAuthRequest extends Request implements HasBody
{
    use HasFormBody;
    use AcceptsJson;

    /**
     * Define the method that the request will use.
     */
    protected Method $method = Method::POST;

    /**
     * Define the base URL for the request.
     */
    public function resolveBaseUrl(): ?string
    {
        return $this->oauthConfig->getBaseUrl();
    }

    /**
     * Define the endpoint for the request.
     */
    public function resolveEndpoint(): string
    {
        return $this->oauthConfig->getTokenEndpoint();
    }

    /**
     * Requires the authorization code and OAuth 2 config.
     *
     * @param array<string> $scopes
     */
    public function __construct(protected OAuthConfig $oauthConfig, protected array $scopes = [], protected string $scopeSeparator = ' ')
    {
        //
    }

    /**
     * Register the default data.
     *
     * @return array{
     *     grant_type: string,
     *     scope: string,
     * }
     */
    public function defaultBody(): array
    {
        return [
            'grant_type' => 'client_credentials',
            'scope' => implode($this->scopeSeparator, array_merge($this->oauthConfig->getDefaultScopes(), $this->scopes)),
        ];
    }

    /**
     * Default authenticator used.
     */
    protected function defaultAuth(): ?Authenticator
    {
        return new BasicAuthenticator($this->oauthConfig->getClientId(), $this->oauthConfig->getClientSecret());
    }
}
