<?php declare(strict_types=1);

namespace Sammyjo20\Saloon\Tests\Fixtures\Requests;

use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Contracts\Body\WithBody;
use Sammyjo20\Saloon\Traits\Body\HasJsonBody;
use Sammyjo20\Saloon\Http\PendingSaloonRequest;
use Sammyjo20\Saloon\Tests\Fixtures\Connectors\TestConnector;

class BootAuthenticatorRequest extends SaloonRequest implements WithBody
{
    use HasJsonBody;

    /**
     * Define the method that the request will use.
     *
     * @var string
     */
    protected string $method = 'GET';

    /**
     * The connector.
     *
     * @var string
     */
    protected string $connector = TestConnector::class;

    /**
     * @return string
     */
    protected function defineEndpoint(): string
    {
        return '/user';
    }

    /**
     * @param PendingSaloonRequest $pendingRequest
     * @return void
     */
    public function boot(PendingSaloonRequest $pendingRequest): void
    {
        $pendingRequest->withTokenAuth('howdy-partner');
    }
}
