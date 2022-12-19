<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Contracts\Body\HasBody;
use Saloon\Traits\Body\HasJsonBody;
use Saloon\Tests\Fixtures\Connectors\PostJsonConnector;

class PostRequest extends Request implements HasBody
{
    use HasJsonBody;

    /**
     * Define the method that the request will use.
     *
     * @var string
     */
    protected Method $method = Method::POST;

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
    public function resolveEndpoint(): string
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
