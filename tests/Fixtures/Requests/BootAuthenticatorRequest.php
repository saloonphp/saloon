<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Contracts\Body\HasBody;
use Saloon\Traits\Body\HasJsonBody;
use Saloon\Contracts\PendingRequest;
use Saloon\Tests\Fixtures\Connectors\TestConnector;

class BootAuthenticatorRequest extends Request implements HasBody
{
    use HasJsonBody;

    /**
     * Define the method that the request will use.
     *
     * @var string
     */
    protected Method $method = Method::GET;

    /**
     * The connector.
     *
     * @var string
     */
    protected string $connector = TestConnector::class;

    /**
     * @return string
     */
    public function resolveEndpoint(): string
    {
        return '/user';
    }

    /**
     * @param PendingRequest $pendingRequest
     * @return void
     */
    public function boot(PendingRequest $pendingRequest): void
    {
        $pendingRequest->withTokenAuth('howdy-partner');
    }
}
