<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use SoapHeader;
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

    /**
     * Default Query Parameters
     *
     * @return array<string, mixed>
     */
    protected function defaultQuery(): array
    {
        return [
            'Fahrenheit' => $this->fahrenheit,
        ];
    }

    /**
     * Default Config
     *
     * @return array<string, mixed>
     */
    protected function defaultConfig(): array
    {
        return [
            'connection_timeout' => 10, // in seconds,
            'stream_context' => stream_context_create([
                'http' => [
                    'user_agent' => 'Test',
                ],
            ]),
        ];
    }

    /**
     * Define the soap headers that will be applied in every request.
     *
     * @return array<string, mixed>
     */
    protected function defaultHeaders(): array
    {
        return [
            new SoapHeader(namespace: 'namespace1', name: 'name1', data: 'data1'),
            'namespace2' => ['name2','data2', false, null],
            'namespace3' => 'name3',
        ];
    }

    public function __construct(public int $fahrenheit)
    {
        //
    }
}
