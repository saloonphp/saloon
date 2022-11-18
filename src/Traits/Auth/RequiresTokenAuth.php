<?php declare(strict_types=1);

namespace Saloon\Traits\Auth;

use Saloon\Contracts\PendingRequest;
use Saloon\Exceptions\MissingAuthenticatorException;

trait RequiresTokenAuth
{
    use RequiresAuth;

    /**
     * @param PendingRequest $pendingRequest
     * @return void
     * @throws MissingAuthenticatorException
     */
    public function bootRequiresTokenAuth(PendingRequest $pendingRequest): void
    {
        $this->bootRequiresAuth($pendingRequest);
    }

    /**
     * Default message.
     *
     * @param PendingRequest $pendingRequest
     * @return string
     */
    protected function getRequiresAuthMessage(PendingRequest $pendingRequest): string
    {
        return sprintf('The "%s" request requires authentication. Please provide authentication using the `withTokenAuth` method or return a default authenticator in your connector/request.', $pendingRequest->getRequest()::class);
    }
}
