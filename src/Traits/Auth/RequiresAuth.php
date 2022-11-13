<?php declare(strict_types=1);

namespace Saloon\Traits\Auth;

use Saloon\Contracts\Authenticator;
use Saloon\Http\PendingSaloonRequest;
use Saloon\Exceptions\MissingAuthenticatorException;

trait RequiresAuth
{
    /**
     * Throw an exception if an authenticator is not on the request while it is booting.
     *
     * @param PendingSaloonRequest $pendingSaloonRequest
     * @return void
     * @throws MissingAuthenticatorException
     */
    public function bootRequiresAuth(PendingSaloonRequest $pendingSaloonRequest): void
    {
        $authenticator = $pendingSaloonRequest->getAuthenticator();

        if (! $authenticator instanceof Authenticator) {
            throw new MissingAuthenticatorException($this->getRequiresAuthMessage($pendingSaloonRequest));
        }
    }

    /**
     * Default message.
     *
     * @param PendingSaloonRequest $pendingRequest
     * @return string
     */
    protected function getRequiresAuthMessage(PendingSaloonRequest $pendingRequest): string
    {
        return sprintf('The "%s" request requires authentication. Please provide an authenticator using the `withAuth` method or return a default authenticator in your connector/request.', $pendingRequest->getRequest()::class);
    }
}
