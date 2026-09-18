<?php
declare(strict_types=1);

namespace Saloon\Http\Auth;

use Saloon\Contracts\Authenticator;
use Saloon\Enums\Method;
use Saloon\Http\PendingRequest;

class OAuth1Authenticator implements Authenticator
{
    public function __construct(
        private readonly string $consumerKey,
        private readonly string $consumerSecret,
        private readonly string $token,
        private readonly string $tokenSecret) {}

    /**
     * Apply the authentication to the request.
     *
     */
    public function set(PendingRequest $pendingRequest): void
    {
        $oauthHeader = $this->generateOAuthHeader($pendingRequest);
        $pendingRequest->headers()
            ->add('Authorization', $oauthHeader);
    }

    private function generateOAuthHeader(PendingRequest $pendingRequest): string
    {
        $method = $pendingRequest->getMethod();
        $url = $pendingRequest->getUrl();
        $params = array_merge($pendingRequest->query()?->all() ?? [], $pendingRequest->body()?->all() ?? []);

        $oauthParams = [
            'oauth_consumer_key' => $this->consumerKey,
            'oauth_token' => $this->token,
            'oauth_nonce' => $this->generateNonce(),
            'oauth_timestamp' => time(),
            'oauth_signature_method' => 'HMAC-SHA1',
            'oauth_version' => '1.0',
        ];
        $baseString = $this->generateBaseString($method, $url, array_merge($params, $oauthParams));
        $signature = $this->generateSignature($baseString);
        $oauthParams['oauth_signature'] = $signature;

        return 'OAuth '.urldecode(http_build_query($oauthParams, '', ', '));

    }

    private function generateBaseString(Method $method, string $url, array $params): string
    {
        ksort($params);

        $query = http_build_query($params, '', '&', PHP_QUERY_RFC3986);

        return strtoupper($method->value).'&'.rawurlencode($url).'&'.rawurlencode($query);
    }

    private function generateSignature(string $baseString): string
    {
        $key = rawurlencode($this->consumerSecret).'&'.rawurlencode($this->tokenSecret);

        return base64_encode(hash_hmac('sha1', $baseString, $key, true));
    }

    private function generateNonce(): string
    {
        return bin2hex(random_bytes(16));
    }
}
