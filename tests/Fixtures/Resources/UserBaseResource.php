<?php

namespace Saloon\Tests\Fixtures\Resources;

use Saloon\Http\BaseResource;
use Saloon\Tests\Fixtures\Requests\UserRequest;

class UserBaseResource extends BaseResource
{
    /**
     * Get User
     *
     * @return array
     * @throws \JsonException
     * @throws \ReflectionException
     * @throws \Throwable
     */
    public function get(): array
    {
        return $this->connector->send(new UserRequest)->array();
    }
}
