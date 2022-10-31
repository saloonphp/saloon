<?php

namespace Sammyjo20\Saloon\Tests\Fixtures\Requests;

use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Traits\Body\HasJsonBody;
use Sammyjo20\Saloon\Interfaces\Data\WithBody;
use Sammyjo20\Saloon\Tests\Fixtures\Connectors\PostJsonConnector;

class PostRequest extends SaloonRequest implements WithBody
{
    use HasJsonBody;

    /**
     * Define the method that the request will use.
     *
     * @var string
     */
    protected string $method = 'POST';

    /**
     * The connector.
     *
     * @var string
     */
    protected string $connector = PostJsonConnector::class;

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
     * @return string[]
     */
    public function defaultBody(): array
    {
        return [
            'requestId' => '2',
        ];
    }
}
