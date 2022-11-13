<?php declare(strict_types=1);

namespace Saloon\Traits\Auth;

use Saloon\Http\Request;
use Saloon\Http\PendingRequest;
use Saloon\Exceptions\MissingAuthenticatorException;

trait RequiresDigestAuth
{
    use RequiresAuth;

    /**
     * @throws MissingAuthenticatorException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     */
    public function bootRequiresDigestAuth(PendingRequest $pendingRequest): void
    {
        $this->bootRequiresAuth($pendingRequest);
    }

    /**
     * Default message.
     *
     * @param Request $request
     * @return string
     */
    protected function getRequiresAuthMessage(PendingRequest $pendingRequest): string
    {
        return sprintf('The "%s" request requires authentication. Please provide authentication using the `withDigestAuth` method or return a default authenticator in your connector/request.', $pendingRequest->getRequest()::class);
    }
}
