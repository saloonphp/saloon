<?php

namespace Sammyjo20\Saloon\Tests\Resources\Responses;

use Sammyjo20\Saloon\Http\SaloonResponse;

class UserResponse extends SaloonResponse
{

    /**
     * @return \Sammyjo20\Saloon\Tests\Resources\Responses\UserData
     */
    public function customCastMethod(): UserData
    {
        return new UserData($this->json('foo'));
    }

    /**
     * @return string|null
     */
    public function foo(): ?string
    {
        return $this->json('foo');
    }
}
