<?php

namespace Sammyjo20\Saloon\Tests\Fixtures\Collections;

use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Http\RequestCollection;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\UserRequest;

class UserCollection extends RequestCollection
{
    /**
     * @return SaloonRequest
     */
    public function get(): SaloonRequest
    {
        return $this->connector->request(new UserRequest);
    }
}
