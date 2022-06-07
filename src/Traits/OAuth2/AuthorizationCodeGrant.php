<?php

namespace Sammyjo20\Saloon\Traits\OAuth2;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Sammyjo20\Saloon\Helpers\OAuth2Config;
use Sammyjo20\Saloon\Helpers\URLHelper;
use Sammyjo20\Saloon\Http\Auth\AccessTokenAuthenticator;
use Sammyjo20\Saloon\Http\OAuth2\GetAccessTokenRequest;
use Sammyjo20\Saloon\Http\SaloonResponse;
use Sammyjo20\Saloon\Traits\Plugins\AlwaysThrowsOnErrors;

trait AuthorizationCodeGrant
{
    /**
     * @var OAuth2Config
     */
    protected OAuth2Config $oauthConfig;

    /**
     * Access the Oauth 2 config
     *
     * @return OAuth2Config
     */
    public function oauthConfig(): OAuth2Config
    {
        return $this->oauthConfig ??= $this->defaultOauthConfig();
    }

    /**
     * Define the default Oauth 2 Config.
     *
     * @return OAuth2Config
     */
    protected function defaultOauthConfig(): OAuth2Config
    {
        return OAuth2Config::make();
    }

    /**
     * Get the Authorization URL.
     *
     * @param array $scopes
     * @param string $scopeSeparator
     * @param string|null $state
     * @return string
     */
    public function getAuthorizationUrl(array $scopes = [], string $scopeSeparator = ' ', string $state = null): string
    {
        $url = URLHelper::join($this->defineBaseUrl(), $this->oauthConfig()->getAuthorizeEndpoint());
        $clientId = $this->oauthConfig()->getClientId();

        $redirectUri = $this->oauthConfig()->getRedirectUri();

        $queryParameters = [
            'response_type' => 'code',
            'scope' => implode($scopeSeparator, $scopes),
            'client_id' => $clientId,
            'redirect_uri' => $redirectUri,
        ];

        if (isset($state)) {
            $queryParameters['state'] = $state;
        }

        $query = http_build_query($queryParameters, '', '&', PHP_QUERY_RFC3986);
        $query = trim($query, '?&');

        $glue = str_contains($url, '?') ? '&' : '?';

        return $url . $glue . $query;
    }

    /**
     * Get the access token.
     *
     * @param string $code
     * @param bool $returnResponse
     * @param bool $throwOnFailure
     * @return SaloonResponse|AccessTokenAuthenticator
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \ReflectionException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonRequestException
     */
    public function getAccessToken(string $code, bool $returnResponse = false, bool $throwOnFailure  = true): SaloonResponse|AccessTokenAuthenticator
    {
        $response = $this->send(new GetAccessTokenRequest($code, $this->oauthConfig()));

        if ($throwOnFailure === true) {
            $response->throw();
        }

        if ($returnResponse === true) {
            return $response;
        }

        $data = $response->object();

        $expiresAt = CarbonImmutable::now()->addSeconds($data->expires_in);

        return new AccessTokenAuthenticator(
            $data->access_token, $data->refresh_token, $expiresAt,
        );
    }
}
