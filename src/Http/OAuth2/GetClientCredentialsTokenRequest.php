<?php

declare(strict_types=1);

namespace Saloon\Http\OAuth2;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasFormBody;
use Saloon\Helpers\OAuth2\OAuthConfig;
use Saloon\Traits\Plugins\AcceptsJson;

class GetClientCredentialsTokenRequest extends Request
{
    use HasFormBody;
    use AcceptsJson;

    /**
     * Define the method that the request will use.
     *
     * @var \Saloon\Enums\Method
     */
    protected Method $method = Method::POST;

    /**
     * Define the endpoint for the request.
     *
     * @return string
     */
    public function resolveEndpoint(): string
    {
        return $this->oauthConfig->getTokenEndpoint();
    }

    /**
     * Requires the authorization code and OAuth 2 config.
     *
     * @param \Saloon\Helpers\OAuth2\OAuthConfig $oauthConfig
     * @param array<string> $scopes
     * @param string $scopeSeparator
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
     *     client_id: string,
     *     client_secret: string,
     *     scope: string,
     * }
     */
    public function defaultBody(): array
    {
        return [
            'grant_type' => 'client_credentials',
            'client_id' => $this->oauthConfig->getClientId(),
            'client_secret' => $this->oauthConfig->getClientSecret(),
            'scope' => implode($this->scopeSeparator, array_merge($this->oauthConfig->getDefaultScopes(), $this->scopes)),
        ];
    }
}
