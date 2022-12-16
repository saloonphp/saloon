<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Body\HasXmlBody;
use Saloon\Contracts\Body\WithBody;

class HasXmlBodyRequest extends Request implements WithBody
{
    use HasXmlBody;

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
     * @return string
     */
    protected function defaultBody(): string
    {
        return '<p>Howdy</p>';
    }
}
