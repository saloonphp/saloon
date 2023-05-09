<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Data\MultipartValue;
use Saloon\Traits\Body\HasMultipartBody;

class HasMultipartBodyRequest extends Request
{
    use HasMultipartBody;

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
            new MultipartValue('nickname', 'Sam', 'user.txt', ['X-Saloon' => 'Yee-haw!']),
        ];
    }
}
