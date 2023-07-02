<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

class SoapRequest extends Request
{
    protected Method $method = Method::POST;

    /**
     * Define the action for the request.
     *
     * @return string
     */
    public function resolveEndpoint(): string
    {
        return 'FahrenheitToCelsius';
    }

    protected function defaultQuery(): array
    {
        return [
            'Fahrenheit' => $this->fahrenheit,
        ];
    }

    public function __construct(public int $fahrenheit)
    {
        //
    }
}
