<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Contracts\Body\HasBody;
use Saloon\Traits\Body\HasFormBody;

class HasFormBodyRequest extends Request implements HasBody
{
    use HasFormBody;

    /**
     * Define the method that the request will use.
     */
    protected Method $method = Method::GET;

    /**
     * Define the endpoint for the request.
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
