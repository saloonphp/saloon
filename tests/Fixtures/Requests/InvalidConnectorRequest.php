<?php declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Http\SaloonRequest;

class InvalidConnectorRequest extends SaloonRequest
{
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
    protected string $connector = 'abc';

    /**
     * Define the endpoint for the request.
     *
     * @return string
     */
    public function defineEndpoint(): string
    {
        return '/user';
    }
}
