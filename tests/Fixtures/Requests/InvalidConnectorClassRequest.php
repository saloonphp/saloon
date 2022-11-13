<?php declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Http\SaloonRequest;

class InvalidConnectorClassRequest extends SaloonRequest
{
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
    protected string $connector = SaloonRequest::class;

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
