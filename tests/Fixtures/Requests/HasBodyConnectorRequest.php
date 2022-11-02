<?php declare(strict_types=1);

namespace Sammyjo20\Saloon\Tests\Fixtures\Requests;

use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Traits\Plugins\HasBody;
use Sammyjo20\Saloon\Tests\Fixtures\Connectors\HasBodyConnector;

class HasBodyConnectorRequest extends SaloonRequest
{
    use HasBody;

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
    protected string $connector = HasBodyConnector::class;

    /**
     * Define the endpoint for the request.
     *
     * @return string
     */
    public function defineEndpoint(): string
    {
        return '/user';
    }

    /**
     * Define the mixed body
     *
     * @return mixed
     */
    public function defineBody(): mixed
    {
        return 'xml';
    }
}
