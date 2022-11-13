<?php declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Http\SaloonRequest;
use Saloon\Traits\Plugins\HasXMLBody;
use Saloon\Tests\Fixtures\Connectors\TestConnector;

class HasXMLRequest extends SaloonRequest
{
    use HasXMLBody;

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

    public function defineXMLBody(): ?string
    {
        return '<xml></xml>';
    }
}
