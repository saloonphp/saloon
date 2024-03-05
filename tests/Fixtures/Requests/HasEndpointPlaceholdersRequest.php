<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Traits\Request\HasEndpointPlaceholders;

class HasEndpointPlaceholdersRequest extends Request
{
    use HasEndpointPlaceholders;

    /**
     * Define endpoint
     */
    protected string $endpoint = '/{user}/post/{id}/{purge}';

    /**
     * Define the HTTP method.
     *
     * @var Method
     */
    protected Method $method = Method::DELETE;

    public function __construct(
        protected string $user,
        protected ?int $id = null,
        protected bool $purge = false,
    ) {
    }
}
