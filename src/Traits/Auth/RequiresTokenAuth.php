<?php declare(strict_types=1);

namespace Sammyjo20\Saloon\Traits\Auth;

use Sammyjo20\Saloon\Http\PendingSaloonRequest;
use Sammyjo20\Saloon\Exceptions\MissingAuthenticatorException;

trait RequiresTokenAuth
{
    use RequiresAuth;

    /**
     * @param PendingSaloonRequest $pendingRequest
     * @return void
     * @throws MissingAuthenticatorException
     */
    public function bootRequiresTokenAuth(PendingSaloonRequest $pendingRequest): void
    {
        $this->bootRequiresAuth($pendingRequest);
    }

    /**
     * Default message.
     *
     * @param PendingSaloonRequest $pendingRequest
     * @return string
     */
    protected function getRequiresAuthMessage(PendingSaloonRequest $pendingRequest): string
    {
        return sprintf('The "%s" request requires authentication. Please provide authentication using the `withTokenAuth` method or return a default authenticator in your connector/request.', $pendingRequest->getRequest()::class);
    }
}
