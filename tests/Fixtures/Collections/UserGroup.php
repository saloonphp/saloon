<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Collections;

use Saloon\Http\Request;
use Saloon\Http\Groups\RequestGroup;
use Saloon\Tests\Fixtures\Requests\UserRequest;

class UserGroup extends RequestGroup
{
    /**
     * @return Request
     */
    public function get(): Request
    {
        return $this->connector->request(new UserRequest);
    }
}
