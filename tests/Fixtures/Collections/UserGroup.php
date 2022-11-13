<?php declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Collections;

use Saloon\Http\SaloonRequest;
use Saloon\Http\Groups\RequestGroup;
use Saloon\Tests\Fixtures\Requests\UserRequest;

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
