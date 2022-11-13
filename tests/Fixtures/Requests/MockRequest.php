<?php declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Http\Request;
use Saloon\Tests\Fixtures\Connectors\TestConnector;

class MockRequest extends Request
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

    /**
     * Default headers on the request
     *
     * @return string[]
     */
    public function defaultHeaders(): array
    {
        return [
            'X-Mock-Header' => 'Mocking',
        ];
    }

    /**
     * @return int[]
     */
    public function defaultConfig(): array
    {
        return [
            'timeout' => 5,
        ];
    }
}
