<?php declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Http\SaloonRequest;
use Saloon\Traits\Auth\RequiresAuth;
use Saloon\Tests\Fixtures\Connectors\TestConnector;

class RequiresAuthRequest extends SaloonRequest
{
    use RequiresAuth;

    /**
     * Define the method that the request will use.
     *
     * @var string|null
     */
    protected string $method = 'GET';

    /**
     * The connector.
     *
     * @var string|null
     */
    protected string $connector = TestConnector::class;

    /**
     * Define the endpoint for the request.
     *
     * @return string
     */
    public function defineEndpoint(): string
    {
        return '/user';
    }

    public function __construct(public ?int $userId = null, public ?int $groupId = null)
    {
        //
    }
}
