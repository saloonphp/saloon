<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Contracts\Body\HasBody;
use Saloon\Traits\Body\HasStreamBody;

class HasStreamBodyRequest extends Request implements HasBody
{
    use HasStreamBody;

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

    protected function defaultBody(): mixed
    {
        $temp = fopen('php://memory', 'rw');

        fwrite($temp, 'Howdy, Partner');

        rewind($temp);

        return $temp;
    }
}
