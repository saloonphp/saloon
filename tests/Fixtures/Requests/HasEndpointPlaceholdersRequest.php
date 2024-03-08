<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Tests\Fixtures\Enums\AnotherEnum;
use Saloon\Tests\Fixtures\Enums\GenderEnum;
use Saloon\Tests\Fixtures\Enums\SomeEnum;
use Saloon\Traits\Request\HasEndpointPlaceholders;

class HasEndpointPlaceholdersRequest extends Request
{
    use HasEndpointPlaceholders;

    /**
     * Define endpoint
     */
    protected string $endpoint = '/{user}/{gender}/{something}/post/{id}/{purge}';

    /**
     * Define the HTTP method.
     *
     * @var Method
     */
    protected Method $method = Method::DELETE;

    public function __construct(
        protected string $user,
        protected GenderEnum $gender,
        protected SomeEnum|AnotherEnum $something,
        protected ?int $id = null,
        protected bool $purge = false,
    ) {
    }
}
