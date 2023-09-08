<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\PendingRequest;
use Saloon\Contracts\Body\HasBody;
use Saloon\Traits\Body\HasJsonBody;
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
     */
    protected string $connector = TestConnector::class;


    public function resolveEndpoint(): string
    {
        return '/user';
    }


    public function boot(PendingRequest $pendingRequest): void
    {
        $pendingRequest->withTokenAuth('howdy-partner');
    }
}
