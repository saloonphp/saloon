<?php

declare(strict_types=1);

namespace Saloon\Http\Auth;

use GuzzleHttp\RequestOptions;
use Saloon\Http\PendingRequest;
use Saloon\Contracts\Authenticator;
use Saloon\Http\Senders\GuzzleSender;
use Saloon\Exceptions\SaloonException;

class CertificateAuthenticator implements Authenticator
{
    /**
     * Constructor
     */
    public function __construct(
        public string  $path,
        public ?string $password = null,
    ) {
        //
    }

    /**
     * Apply the authentication to the request.
     *
     * @throws \Saloon\Exceptions\SaloonException
     */
    public function set(PendingRequest $pendingRequest): void
    {
        if (! $pendingRequest->getConnector()->sender() instanceof GuzzleSender) {
            throw new SaloonException('The CertificateAuthenticator is only supported when using the GuzzleSender.');
        }

        // See: https://docs.guzzlephp.org/en/stable/request-options.html#cert

        $path = $this->path;
        $password = $this->password;

        $certificate = is_string($password) ? [$path, $password] : $path;

        $pendingRequest->config()->add(RequestOptions::CERT, $certificate);
    }
}
