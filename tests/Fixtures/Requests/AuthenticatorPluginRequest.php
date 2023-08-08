<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Plugins\AuthenticatorPlugin;

class AuthenticatorPluginRequest extends Request
{
    use AuthenticatorPlugin;

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

    
    public function __construct(public ?int $userId = null, public ?int $groupId = null)
    {
        //
    }

    
    public function resolveEndpoint(): string
    {
        return '/user';
    }
}
