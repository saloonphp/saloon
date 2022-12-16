<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Contracts\Body\WithBody;
use Saloon\Traits\Body\HasJsonBody;

class HasJsonBodyRequest extends Request implements WithBody
{
    use HasJsonBody;

    /**
     * Define the method that the request will use.
     *
     * @var Method
     */
    protected Method $method = Method::GET;

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
     * Default Body
     *
     * @return string[]
     */
    protected function defaultBody(): array
    {
        return [
            'name' => 'Sam',
            'catchphrase' => 'Yeehaw!',
        ];
    }
}
