<?php declare(strict_types=1);

namespace Sammyjo20\Saloon\Tests\Fixtures\Collections;

use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Http\Groups\RequestGroup;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\UserRequest;

class UserGroup extends RequestGroup
{
    /**
     * @return SaloonRequest
     */
    public function get(): SaloonRequest
    {
        return $this->connector->request(new UserRequest);
    }
}
