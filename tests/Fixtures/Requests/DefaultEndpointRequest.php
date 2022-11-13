<?php declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Http\Request;
use Saloon\Tests\Fixtures\Connectors\TestConnector;

class DefaultEndpointRequest extends Request
{
    /**
     * Define the method that the request will use.
     *
     * @var string|null
     */
    protected string $method = 'POST';

    /**
     * The connector.
     *
     * @var string|null
     */
    protected string $connector = TestConnector::class;

    /**
     * @return string
     */
    protected function defineEndpoint(): string
    {
        return '';
    }
}
